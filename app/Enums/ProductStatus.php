<?php

namespace App\Enums;

use App\Enums\Extras\Labels;
use App\Enums\Extras\Values;

enum ProductStatus: int
{
    use Labels, Values;

    case Disabled = 0;
    case Active = 1;

    public static function map(int $value): string
    {
        return match (self::from($value)) {
            self::Disabled => 'Disabled',
            self::Active => 'Active',
        };
    }
}
