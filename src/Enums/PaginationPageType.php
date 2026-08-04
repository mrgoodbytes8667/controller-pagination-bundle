<?php

namespace Bytes\ControllerPaginationBundle\Enums;

use Bytes\EnumSerializerBundle\Enums\StringBackedEnumInterface;
use Bytes\EnumSerializerBundle\Enums\StringBackedEnumTrait;

enum PaginationPageType: string implements StringBackedEnumInterface
{
    use StringBackedEnumTrait;

    case FIRST = 'first';
    case LAST = 'last';
    case PREV = 'prev';
    case NEXT = 'next';
    case PLACEHOLDER = 'placeholder';
    case PAGE = 'page';

    /**
     * @return PaginationPageType[]
     */
    public static function getTraversalTypes(): array
    {
        return [
            PaginationPageType::FIRST,
            PaginationPageType::LAST,
            PaginationPageType::PREV,
            PaginationPageType::NEXT
        ];
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return match ($this) {
            PaginationPageType::PAGE => '',
            PaginationPageType::FIRST, PaginationPageType::PREV => 'fa7-solid:arrow-left',
            PaginationPageType::LAST, PaginationPageType::NEXT => 'fa7-solid:arrow-right',
            PaginationPageType::PLACEHOLDER => 'fa7-solid:ellipsis',
        };
    }

    /**
     * @return bool
     */
    public function isTraversalType(): bool
    {
        return match ($this) {
            PaginationPageType::FIRST, PaginationPageType::LAST, PaginationPageType::PREV, PaginationPageType::NEXT => true,
            default => false,
        };
    }

    /**
     * @return string
     */
    public function getIndex(): string
    {
        return match ($this) {
            PaginationPageType::FIRST => '0-',
            PaginationPageType::LAST => '9-',
            PaginationPageType::PREV => '1-',
            PaginationPageType::NEXT => '8-',
            default => '5-',
        };
    }
}
