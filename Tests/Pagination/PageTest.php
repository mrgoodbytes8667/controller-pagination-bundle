<?php

namespace Bytes\ControllerPaginationBundle\Tests\Pagination;

use Bytes\Common\Faker\TestFakerTrait;
use Bytes\ControllerPaginationBundle\Enums\PaginationPageType;
use Bytes\ControllerPaginationBundle\Pagination\Page;
use Bytes\Tests\Common\DataProvider\BooleanProviderTrait;
use Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PageTest extends TestCase
{
    use TestFakerTrait, BooleanProviderTrait;

    /**
     * @dataProvider provideIndex
     * @param mixed $index
     */
    public function testGetIndex($index)
    {
        $page = $this->createPage();
        $this->assertEquals('5-0000', $page->getIndex());
    }

    /**
     * @return Generator
     */
    public function provideIndex()
    {
        $this->setupFaker();
        yield [$this->faker->word()];
    }

    /**
     * @dataProvider providePageType
     * @param mixed $pageType
     */
    public function testGetSetPageType($pageType)
    {
        $page = $this->createPage();
        $this->assertInstanceOf(Page::class, $page->setPageType($pageType));
        $this->assertEquals($pageType, $page->getPageType());
    }

    /**
     * @return Generator
     */
    public function providePageType()
    {
        $this->setupFaker();
        yield [PaginationPageType::PAGE];
    }

    /**
     * @dataProvider provideRoute
     * @param mixed $route
     */
    public function testGetSetRoute($route)
    {
        $page = $this->createPage();
        $this->assertEmpty($page->getRoute());
        $this->assertInstanceOf(Page::class, $page->setRoute($route));
        $this->assertEquals($route, $page->getRoute());
    }

    /**
     * @return Generator
     */
    public function provideRoute()
    {
        $this->setupFaker();
        yield [$this->faker->word()];
    }

    /**
     * @dataProvider provideNumber
     * @param mixed $number
     */
    public function testGetSetNumber($number)
    {
        $page = $this->createPage();
        $this->assertNull($page->getNumber());
        $this->assertInstanceOf(Page::class, $page->setNumber(null));
        $this->assertNull($page->getNumber());
        $this->assertInstanceOf(Page::class, $page->setNumber($number));
        $this->assertEquals($number, $page->getNumber());
    }

    /**
     * @return Generator
     */
    public function provideNumber()
    {
        $this->setupFaker();
        yield [$this->faker->randomDigitNotNull()];
    }

    /**
     * @dataProvider provideParameters
     * @param mixed $parameters
     */
    public function testGetSetParameters($parameters)
    {
        $page = $this->createPage();
        $this->assertEmpty($page->getParameters());
        $this->assertInstanceOf(Page::class, $page->setParameters($parameters));
        $this->assertEquals($parameters, $page->getParameters());
    }

    /**
     * @return Generator
     */
    public function provideParameters()
    {
        $this->setupFaker();
        yield [$this->faker->words()];
    }

    /**
     * @dataProvider provideBooleans
     * @param bool $active
     */
    public function testIsSetActive($active)
    {
        $page = $this->createPage();
        $this->assertTrue($page->isActive());
        $this->assertInstanceOf(Page::class, $page->setActive($active));
        $this->assertEquals($active, $page->isActive());
    }

    public function testActive()
    {
        $page = $this->createPage();
        $this->assertTrue($page->isActive());
        $this->assertInstanceOf(Page::class, $page->setInactive());
        $this->assertFalse($page->isActive());
    }

    /**
     * @dataProvider provideUrlGenerator
     * @param mixed $urlGenerator
     */
    public function testGetSetUrlGenerator($urlGenerator)
    {
        $page = $this->createPage();
        $this->assertNull($page->getUrlGenerator());
        $page->setUrlGenerator($urlGenerator);
        $this->assertEquals($urlGenerator, $page->getUrlGenerator());
    }

    /**
     * @return Generator
     */
    public function provideUrlGenerator()
    {
        yield [$this->getMockBuilder(UrlGeneratorInterface::class)->getMock()];
    }

    /**
     * @return void
     */
    public function testGetPlaceholderUrl()
    {
        $page = Page::createPlaceholder(1);
        $this->assertEquals('#', $page->getUrl());
    }

    /**
     * @return Page
     */
    private function createPage(): Page
    {
        return new Page(PaginationPageType::PAGE);
    }

    public function testCreate() {
        $this->assertInstanceOf(Page::class, Page::createPage($this->faker->randomDigitNotNull(), $this->faker->word()));
        $this->assertInstanceOf(Page::class, Page::createTraversal($this->faker->randomElement(PaginationPageType::getTraversalTypes())));
        $this->assertInstanceOf(Page::class, Page::createPlaceholder($this->faker->randomDigitNotNull()));
    }

    /**
     * @dataProvider providePage
     * @dataProvider providePlaceholder
     * @param $pageType
     * @return void
     */
    public function testCreateTraversalInvalidType($pageType) {
        $this->expectException(\InvalidArgumentException::class);
        Page::createTraversal($pageType);
    }

    public function testIsCurrentPage() {
        $page = $this->createPage();
        $this->assertFalse($page->isCurrentPage());
        $page->setCurrentPage(true);
        $this->assertTrue($page->isCurrentPage());
        $page->setCurrentPage(false);
        $this->assertFalse($page->isCurrentPage());
        $page->setCurrentPage(true);
        $this->assertTrue($page->isCurrentPage());
        $page->resetCurrentPage();
        $this->assertFalse($page->isCurrentPage());
        $page->setCurrentPage(true);
        $this->assertTrue($page->isCurrentPage());
        $page->resetFirstLastCurrent();
        $this->assertFalse($page->isCurrentPage());
    }

    public function testIsFirstPage() {
        $page = $this->createPage();
        $this->assertFalse($page->isFirstPage());
        $page->setFirstPage(true);
        $this->assertTrue($page->isFirstPage());
        $page->setFirstPage(false);
        $this->assertFalse($page->isFirstPage());
        $page->setFirstPage(true);
        $this->assertTrue($page->isFirstPage());
        $page->resetFirstPage();
        $this->assertFalse($page->isFirstPage());
        $page->setFirstPage(true);
        $this->assertTrue($page->isFirstPage());
        $page->resetFirstLastCurrent();
        $this->assertFalse($page->isFirstPage());
    }

    public function testIsLastPage() {
        $page = $this->createPage();
        $this->assertFalse($page->isLastPage());
        $page->setLastPage(true);
        $this->assertTrue($page->isLastPage());
        $page->setLastPage(false);
        $this->assertFalse($page->isLastPage());
        $page->setLastPage(true);
        $this->assertTrue($page->isLastPage());
        $page->resetLastPage();
        $this->assertFalse($page->isLastPage());
        $page->setLastPage(true);
        $this->assertTrue($page->isLastPage());
        $page->resetFirstLastCurrent();
        $this->assertFalse($page->isLastPage());
    }

    /**
     * @return Generator
     */
    public function providePlaceholder(): Generator
    {
        yield 'Placeholder' => [PaginationPageType::PLACEHOLDER];
    }

    /**
     * @return Generator
     */
    public function providePage(): Generator
    {
        yield 'Page' => [PaginationPageType::PAGE];
    }
}