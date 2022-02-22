<?php

namespace Bytes\ControllerPaginationBundle\Pagination;

use Bytes\ControllerPaginationBundle\Enums\PaginationPageType;
use Doctrine\Common\Collections\ArrayCollection;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaginationHelper
{
    /**
     * @var ArrayCollection<string, Page>
     */
    private ArrayCollection $pages;

    /**
     * @var Request|null
     */
    private $request;

    /**
     * @param UrlGeneratorInterface $urlGenerator
     * @param int $beginOffset
     * @param int $endOffset
     * @param int $currentOffset
     * @param array $parameterAllowlist
     */
    public function __construct(private UrlGeneratorInterface $urlGenerator, private int $beginOffset = 1, private int $endOffset = 1, private int $currentOffset = 1, private array $parameterAllowlist = [])
    {
        $this->pages = new ArrayCollection();
    }

    /**
     * @param PaginationPageType[] $pageTypes
     * @param string|null $route
     * @param array $parameters
     * @return $this
     */
    public function addTraversals(array $pageTypes, ?string $route = null, array $parameters = []): self
    {
        foreach ($pageTypes as $pageType) {
            $this->addTraversal($pageType, $this->replaceRoute($route), $parameters);
        }
        return $this;
    }

    /**
     * @param PaginationPageType $pageType
     * @param string|null $route
     * @param array $parameters
     * @return $this
     */
    public function addTraversal(PaginationPageType $pageType, ?string $route = null, array $parameters = []): self
    {
        $page = Page::createTraversal($pageType, $this->replaceRoute($route), $this->replaceParams($parameters), $this->urlGenerator);
        $this->addIfNotExists($page);

        return $this;
    }

    /**
     * @param string|null $route
     * @return mixed|string|null
     */
    private function replaceRoute(?string $route = null)
    {
        if (empty($this->request)) {
            return $route;
        } else {
            return $route ?? $this->request->attributes->get('_route');
        }
    }

    /**
     * @param array $params
     * @return array
     */
    private function replaceParams(array $params = []): array
    {
        if (empty($this->request)) {
            return $params;
        } else {
            return $params ?: array_merge(
                $this->request->attributes->get('_route_params'),
                $this->filterParams($this->request->query->all())
            );
        }
    }

    /**
     * @param array $params
     * @return array
     */
    private function filterParams(array $params): array
    {
        $params = Arr::except($this->request->query->all(), ['page']);
        if (!empty($this->parameterAllowlist)) {
            $params = Arr::where($params, function ($value, $key) {
                return in_array($key, $this->parameterAllowlist);
            });
        }

        return $params;
    }

    /**
     * @param Page $page
     * @return $this
     */
    private function addIfNotExists(Page $page): self
    {
        if (!$this->pages->containsKey($page->getIndex())) {
            $this->pages->set($page->getIndex(), $page);
        }

        return $this;
    }

    /**
     * @param UrlGeneratorInterface $urlGenerator
     * @required
     */
    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator): void
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param int $number
     * @param string|null $route
     * @param array $parameters
     * @return $this
     */
    public function addPage(int $number, ?string $route = null, array $parameters = []): self
    {
        $this->add($number, $route, $parameters, true, true);

        return $this;
    }

    /**
     * @param int $number
     * @param string|null $route
     * @param array $parameters
     * @param bool $resetFirstLastCurrent
     * @param bool $calculateFirstLastCurrent
     * @return ArrayCollection<string, Page>
     */
    private function add(int $number, ?string $route = null, array $parameters = [], bool $resetFirstLastCurrent = false, bool $calculateFirstLastCurrent = false): ArrayCollection
    {
        $page = Page::createPage($number, $this->replaceRoute($route), $this->replaceParams($parameters), $this->urlGenerator);
        if ($resetFirstLastCurrent) {
            $this->resetFirstLastCurrent();
            if ($calculateFirstLastCurrent) {
                $page->setCurrentPage();
            }
        }
        $return = $this->addIfNotExists($page);
        if ($resetFirstLastCurrent && $calculateFirstLastCurrent) {
            $this->setFirstPage();
            $this->setLastPage();
        }

        return $this->pages;
    }

    /**
     * @return ArrayCollection<string, Page>
     */
    private function resetFirstLastCurrent(): ArrayCollection
    {
        foreach ($this->pages->filter(function (Page $value) {
            return $value->isFirstPage() || $value->isLastPage() || $value->isCurrentPage();
        }) as $page) {
            /** @var Page $page */
            $page->resetFirstLastCurrent();
        }

        return $this->pages;
    }

    /**
     * @return Page
     */
    private function setFirstPage(): Page
    {
        return $this->calculateFirstPage()->setFirstPage();
    }

    /**
     * @return Page
     */
    public function calculateFirstPage(): Page
    {
        return $this->pages->filter(function (Page $value) {
            return $value->getPageType() === PaginationPageType::PAGE;
        })->first();
    }

    /**
     * @return Page
     */
    private function setLastPage(): Page
    {
        return $this->calculateLastPage()->setLastPage();
    }

    /**
     * @return Page
     */
    public function calculateLastPage(): Page
    {
        return $this->pages->filter(function (Page $value) {
            return $value->getPageType() === PaginationPageType::PAGE;
        })->last();
    }

    /**
     * @param int $finish
     * @param string|null $route
     * @param int $start
     * @param array $parameters
     * @return $this
     */
    public function addRange(int $finish, ?string $route = null, int $start = 1, array $parameters = []): self
    {
        foreach (range($start, $finish) as $number) {
            $this->add($number, $route, array_merge($this->replaceParams($parameters), ['page' => $number]), false, false);
        }
        $this->resetFirstLastCurrent();
        $this->getPage($start)->setCurrentPage();
        $this->setFirstPage();
        $this->setLastPage();

        return $this;
    }

    /**
     * @param Page|PaginationPageType|int $number
     * @return Page|null
     */
    public function getPage(Page|PaginationPageType|int $number): ?Page
    {
        if ($number instanceof Page) {
            return $this->pages->get($number->getIndex());
        }
        if ($number instanceof PaginationPageType) {
            if (!$number->isTraversalType()) {
                throw new InvalidArgumentException();
            }
            return $this->pages->get(Page::buildIndex($number));
        }
        return $this->pages->get(Page::buildPageIndex($number));
    }

    /**
     * @param int $currentPage
     * @param int|null $beginOffset
     * @param int|null $endOffset
     * @param int|null $currentOffset
     * @return Page[]
     */
    public function getPages(int $currentPage, ?int $beginOffset = null, ?int $endOffset = null, ?int $currentOffset = null): array
    {
        $this->setCurrentPage($currentPage);
        $this->populatePlaceholders($currentPage, $beginOffset, $endOffset, $currentOffset);

        return $this->sortPages($this->pages);
    }

    /**
     * @param Page|int $number
     * @return Page
     */
    private function setCurrentPage(Page|int $number): Page
    {
        $this->resetCurrent();
        if (!($number instanceof Page)) {
            $number = $this->getPage($number);
        }
        $page = $number->setCurrentPage();
        $this->calculateTraversalProperties();

        return $this->getPage($page);
    }

    /**
     * @return ArrayCollection<string, Page>
     */
    private function resetCurrent(): ArrayCollection
    {
        foreach ($this->pages->filter(function (Page $value) {
            return $value->isCurrentPage();
        }) as $page) {
            /** @var Page $page */
            $page->resetCurrentPage();
        }

        return $this->pages;
    }

    /**
     * @return $this
     */
    private function calculateTraversalProperties(): self
    {
        if ($this->getFirstPage() === $this->getCurrentPage()) {
            $this->getPage(PaginationPageType::PREV)?->setInactive();
            $this->getPage(PaginationPageType::FIRST)?->setInactive();
        } else {
            $this->getPage(PaginationPageType::FIRST)?->mergeParameters(['page' => $this->getFirstPage()?->getNumber()]);
            $this->getPage(PaginationPageType::PREV)?->mergeParameters(['page' => $this->getCurrentPage()?->getNumber() - 1]);
        }
        if ($this->getLastPage() === $this->getCurrentPage()) {
            $this->getPage(PaginationPageType::NEXT)?->setInactive();
            $this->getPage(PaginationPageType::LAST)?->setInactive();
        } else {
            $this->getPage(PaginationPageType::LAST)?->mergeParameters(['page' => $this->getLastPage()?->getNumber()]);
            $this->getPage(PaginationPageType::NEXT)?->mergeParameters(['page' => $this->getCurrentPage()?->getNumber() + 1]);
        }

        return $this;
    }

    /**
     * @return Page
     */
    public function getFirstPage(): Page
    {
        return $this->pages->filter(function (Page $value) {
            return $value->getPageType() === PaginationPageType::PAGE && $value->isFirstPage();
        })->first();
    }

    /**
     * @return Page
     */
    public function getCurrentPage(): Page
    {
        return $this->pages->filter(function (Page $value) {
            return $value->getPageType() === PaginationPageType::PAGE && $value->isCurrentPage();
        })->first();
    }

    /**
     * @return Page
     */
    public function getLastPage(): Page
    {
        return $this->pages->filter(function (Page $value) {
            return $value->getPageType() === PaginationPageType::PAGE && $value->isLastPage();
        })->first();
    }

    /**
     * @param int $currentPage
     * @param int|null $beginOffset
     * @param int|null $endOffset
     * @param int|null $currentOffset
     * @return $this
     */
    public function populatePlaceholders(int $currentPage, ?int $beginOffset = null, ?int $endOffset = null, ?int $currentOffset = null): self
    {
        $beginOffset ??= $this->beginOffset;
        $endOffset ??= $this->endOffset;
        $currentOffset ??= $this->currentOffset;

        //$this->rebuildSorted();

        $beginToCurrentStart = 1 + $beginOffset + 1;
        $beginToCurrentEnd = $currentPage - $currentOffset - 1;

        if ($beginToCurrentEnd - $beginToCurrentStart >= 1) {
            $this->createPlaceholder($beginToCurrentStart, $beginToCurrentEnd);
        }

        $currentToEndStart = $currentPage + $currentOffset + 1;
        $currentToEndEnd = $this->calculateLastPage()->getNumber() - $endOffset - 1;

        if ($currentToEndEnd - $currentToEndStart >= 1) {
            $this->createPlaceholder($currentToEndStart, $currentToEndEnd);
        }

        //$this->rebuildSorted();

        //return $this->sortPages($this->pages);

        return $this;
    }

    /**
     * @param int $min
     * @param int $max
     * @return $this
     */
    public function createPlaceholder(int $min, int $max): self
    {
        $placeholder = Page::createPlaceholder($min);
        $index = $placeholder->getIndex();
        $this->pages->set($index, $placeholder);
        if ($max > $min) {
            foreach (range($min + 1, $max) as $i) {
                $index = Page::buildPageIndex($i);
                $this->pages->remove($index);
            }
        }

        return $this;
    }

    /**
     * @param ArrayCollection<string, Page>|Page[] $pages
     * @return Page[]
     */
    private function sortPages(ArrayCollection|array $pages): array
    {
        if ($pages instanceof ArrayCollection) {
            $pages = $pages->toArray();
        }
        return array_values(Arr::sort($pages, function ($value, $index) {
            return $index;
        }));
    }

    /**
     * @return Page[]
     */
    public function getAllPages(): array
    {
        $pages = $this->pages->toArray();

        return $this->sortPages($pages);
    }

    /**
     * @param string[] $parameterAllowlist
     * @return $this
     */
    public function setParameterAllowlist(array $parameterAllowlist): self
    {
        $this->parameterAllowlist = $parameterAllowlist;
        return $this;
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request): self
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @param int $beginOffset
     */
    public function setBeginOffset(int $beginOffset): void
    {
        $this->beginOffset = $beginOffset;
    }

    /**
     * @param int $endOffset
     */
    public function setEndOffset(int $endOffset): void
    {
        $this->endOffset = $endOffset;
    }

    /**
     * @param int $currentOffset
     */
    public function setCurrentOffset(int $currentOffset): void
    {
        $this->currentOffset = $currentOffset;
    }

    /**
     * @return $this
     */
    private function populateUrlGenerator(): self
    {
        foreach ($this->pages as $page) {
            $page->setUrlGenerator($this->urlGenerator);
        }

        return $this;
    }
}
