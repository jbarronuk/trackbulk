<?php

namespace App\Enums;

use App\Enums\Extras\Labels;
use App\Enums\Extras\Values;

enum UserType: int
{
    use Labels, Values;

    case Disabled = 0;
    case Admin = 1;
    case Standard = 2;

    public static function map(int $value): string
    {
        return match (self::from($value)) {
            self::Disabled => 'Disabled',
            self::Admin => 'Admin',
            self::Standard => 'Standard',
        };
    }
}
