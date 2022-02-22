<?php

namespace Bytes\ControllerPaginationBundle\Enums;

use Bytes\EnumSerializerBundle\Enums\StringBackedEnumInterface;
use Bytes\EnumSerializerBundle\Enums\StringBackedEnumTrait;

enum PaginationPageType: string implements StringBackedEnumInterface
{
    use StringBackedEnumTrait;

    case FIRST = 'fa-solid fa-angles-left';
    case LAST = 'fa-solid fa-angles-right';
    case PREV = 'fa-solid fa-angle-left';
    case NEXT = 'fa-solid fa-angle-right';
    case PLACEHOLDER = 'fa-solid fa-ellipsis';
    case PAGE = 'page';

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return match ($this) {
            PaginationPageType::PAGE => '',
            default => $this->value,
        };
    }

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
     * @return bool
     */
    public function isTraversalType(): bool {
        return match ($this) {
            PaginationPageType::FIRST, PaginationPageType::LAST, PaginationPageType::PREV, PaginationPageType::NEXT => true,
            default => false,
        };
    }


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
