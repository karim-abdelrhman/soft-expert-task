<?php

namespace App\Enums;

enum Status : int
{
    case PENDING = 0;
    case COMPLETED = 1;
    case CANCELLED = 2;

    public static function labels() : array
    {
        return [
            self::PENDING->value => __('pending'),
            self::COMPLETED->value => __('completed'),
            self::CANCELLED->value => __('cancelled'),
        ];
    }

    public static function fromLabel(string $value) : int|null
    {
        $key = array_search($value, self::labels(), true);
        return $key === false ? null : $key;
    }
}
