<?php

namespace Bytes\ControllerPaginationBundle\Pagination;

use Bytes\ControllerPaginationBundle\Enums\PaginationPageType;
use InvalidArgumentException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function Symfony\Component\String\u;

class Page
{
    /**
     * @var PaginationPageType
     */
    private $pageType;

    /**
     * @var bool
     */
    private $currentPage = false;

    /**
     * @var bool
     */
    private $firstPage = false;

    /**
     * @var bool
     */
    private $lastPage = false;

    /**
     * @var bool
     */
    private $active = true;

    /**
     * @param PaginationPageType $pageType
     * @param string|null $route
     * @param int|null $number
     * @param array $parameters
     * @param UrlGeneratorInterface|null $urlGenerator
     */
    public function __construct(PaginationPageType $pageType, private ?string $route = '', private ?int $number = null, private array $parameters = [], private ?UrlGeneratorInterface $urlGenerator = null)
    {
        $this->setPageType($pageType);
    }

    /**
     * @param int $number
     * @param string $route
     * @param array $parameters
     * @param UrlGeneratorInterface|null $urlGenerator
     * @return static
     */
    public static function createPage(int $number, string $route, array $parameters = [], ?UrlGeneratorInterface $urlGenerator = null): static
    {
        return new static(PaginationPageType::PAGE, route: $route, number: $number, parameters: $parameters, urlGenerator: $urlGenerator);
    }

    public static function buildPageIndex(int $number): string
    {
        return static::buildIndex(PaginationPageType::PAGE, $number);
    }

    /**
     * @param PaginationPageType $pageType
     * @param string|null $route
     * @param array $parameters
     * @param UrlGeneratorInterface|null $urlGenerator
     * @return static
     */
    public static function createTraversal(PaginationPageType $pageType, ?string $route = null, array $parameters = [], ?UrlGeneratorInterface $urlGenerator = null): static
    {
        if (!$pageType->isTraversalType()) {
            throw new InvalidArgumentException();
        }
        return new static($pageType, route: $route, parameters: $parameters, urlGenerator: $urlGenerator);
    }

    /**
     * @param int $position
     * @return static
     */
    public static function createPlaceholder(int $position): static
    {
        $static = new static(PaginationPageType::PLACEHOLDER, number: $position);
        return $static->setActive(false);
    }

    /**
     * @return bool
     */
    public function isCurrentPage(): bool
    {
        return $this->currentPage;
    }

    /**
     * @param bool $currentPage
     * @return $this
     */
    public function setCurrentPage(bool $currentPage = true): self
    {
        $this->currentPage = $currentPage;
        return $this;
    }

    /**
     * @return $this
     */
    public function resetCurrentPage(): self
    {
        return $this->setCurrentPage(false);
    }

    /**
     * @return bool
     */
    public function isFirstPage(): bool
    {
        return $this->firstPage;
    }

    /**
     * @param bool $firstPage
     * @return $this
     */
    public function setFirstPage(bool $firstPage = true): self
    {
        $this->firstPage = $firstPage;
        return $this;
    }

    /**
     * @return $this
     */
    public function resetFirstPage(): self
    {
        return $this->setFirstPage(false);
    }

    /**
     * @return bool
     */
    public function isLastPage(): bool
    {
        return $this->lastPage;
    }

    /**
     * @param bool $lastPage
     * @return $this
     */
    public function setLastPage(bool $lastPage = true): self
    {
        $this->lastPage = $lastPage;
        return $this;
    }

    /**
     * @return $this
     */
    public function resetLastPage(): self
    {
        return $this->setLastPage(false);
    }

    /**
     * @return $this
     */
    public function resetFirstLastCurrent(): self
    {
        return $this->setFirstPage(false)
            ->setLastPage(false)
            ->setCurrentPage(false);
    }

    public function getIndex(): string
    {
        return static::buildIndex($this->pageType, $this->number);
    }

    public static function buildIndex(PaginationPageType $pageType, ?int $number = null): string
    {
        return $pageType->getIndex() . (!empty($number) ? u($number)->padStart(4, '0')->toString() : '0000');
    }

    /**
     * @return PaginationPageType
     */
    public function getPageType(): PaginationPageType
    {
        return $this->pageType;
    }

    /**
     * @param PaginationPageType $pageType
     * @return Page
     */
    public function setPageType(PaginationPageType $pageType): Page
    {
        $this->pageType = $pageType;
        return $this;
    }

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * @param string $route
     * @return Page
     */
    public function setRoute(string $route): Page
    {
        $this->route = $route;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getNumber(): ?int
    {
        return $this->number;
    }

    /**
     * @param int|null $number
     * @return Page
     */
    public function setNumber(?int $number): Page
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     * @return Page
     */
    public function setParameters(array $parameters): Page
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * @param array $parameters
     * @return $this
     */
    public function mergeParameters(array $parameters): self
    {
        return $this->setParameters(array_merge($this->parameters, $parameters));
    }

    /**
     * @return string
     */
    public function getIconOrPageNumber(): string
    {
        if ($this->pageType === PaginationPageType::PAGE) {
            return $this->number;
        } else {
            return $this->pageType->getIcon();
        }
    }

    /**
     * @return string
     */
    public function getIconHtmlOrPageNumber(): string
    {
        if ($this->pageType === PaginationPageType::PAGE) {
            return $this->number;
        } else {
            return sprintf('<i class="%s"></i>', $this->pageType->getIcon());
        }
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return $this
     */
    public function setActive(bool $active = true): self
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return $this
     */
    public function setInactive(): self
    {
        return $this->setActive(false);
    }

    /**
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator): void
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function getUrl(): string
    {
        if ($this->pageType === PaginationPageType::PLACEHOLDER || empty($this->urlGenerator) || empty($this->route) || !$this->active) {
            return '#';
        }

        return $this->urlGenerator->generate($this->route, $this->parameters);
    }

    /**
     * @return UrlGeneratorInterface|null
     */
    public function getUrlGenerator(): ?UrlGeneratorInterface
    {
        return $this->urlGenerator;
    }
}
