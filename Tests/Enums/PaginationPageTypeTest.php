<?php

namespace Bytes\ControllerPaginationBundle\Tests\Enums;

use Bytes\ControllerPaginationBundle\Enums\PaginationPageType;
use Generator;
use PHPUnit\Framework\TestCase;

class PaginationPageTypeTest extends TestCase
{
    /**
     * @dataProvider provideFirst
     * @dataProvider provideLast
     * @dataProvider providePrev
     * @dataProvider provideNext
     * @dataProvider providePlaceholder
     * @param PaginationPageType $pageType
     * @return void
     */
    public function testGetIcon(PaginationPageType $pageType)
    {
        $this->assertEquals($pageType->value, $pageType->getIcon());
    }

    /**
     * @dataProvider providePage
     * @param PaginationPageType $pageType
     * @return void
     */
    public function testGetIconForPage(PaginationPageType $pageType)
    {
        $this->assertEquals('', $pageType->getIcon());
    }

    /**
     * @dataProvider provideFirst
     * @dataProvider provideLast
     * @dataProvider providePrev
     * @dataProvider provideNext
     * @param PaginationPageType $pageType
     * @return void
     */
    public function testIsTraversable(PaginationPageType $pageType)
    {
        $this->assertTrue($pageType->isTraversalType());

        $this->assertIsNumeric(array_search($pageType, PaginationPageType::getTraversalTypes()));
    }

    /**
     * @dataProvider providePlaceholder
     * @dataProvider providePage
     * @param PaginationPageType $pageType
     * @return void
     */
    public function testIsNotTraversable(PaginationPageType $pageType)
    {
        $this->assertFalse($pageType->isTraversalType());

        $this->assertFalse(array_search($pageType, PaginationPageType::getTraversalTypes()));
    }

    /**
     * @return Generator
     */
    public function provideFirst(): Generator
    {
        yield 'First' => [PaginationPageType::FIRST];
    }

    /**
     * @return Generator
     */
    public function provideLast(): Generator
    {
        yield 'Last' => [PaginationPageType::LAST];
    }

    /**
     * @return Generator
     */
    public function providePrev(): Generator
    {
        yield 'Prev' => [PaginationPageType::PREV];
    }

    /**
     * @return Generator
     */
    public function provideNext(): Generator
    {
        yield 'Next' => [PaginationPageType::NEXT];
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

    /**
     * @dataProvider providePlaceholder
     * @dataProvider providePage
     * @param PaginationPageType $pageType
     * @return void
     */
    public function testGetIndex(PaginationPageType $pageType)
    {
        $this->assertEquals('5-', $pageType->getIndex());
    }

    /**
     * @dataProvider provideFirst
     * @param PaginationPageType $pageType
     * @return void
     */
    public function testGetIndexFirst(PaginationPageType $pageType)
    {
        $this->assertEquals('0-', $pageType->getIndex());
    }

    /**
     * @dataProvider provideLast
     * @param PaginationPageType $pageType
     * @return void
     */
    public function testGetIndexLast(PaginationPageType $pageType)
    {
        $this->assertEquals('9-', $pageType->getIndex());
    }

    /**
     * @dataProvider providePrev
     * @param PaginationPageType $pageType
     * @return void
     */
    public function testGetIndexPrev(PaginationPageType $pageType)
    {
        $this->assertEquals('1-', $pageType->getIndex());
    }

    /**
     * @dataProvider provideNext
     * @param PaginationPageType $pageType
     * @return void
     */
    public function testGetIndexNext(PaginationPageType $pageType)
    {
        $this->assertEquals('8-', $pageType->getIndex());
    }
}
