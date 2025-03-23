<?php

namespace App\Enums;

use App\Enums\Extras\Labels;
use App\Enums\Extras\Values;

enum TrackingType: int
{
    use Labels, Values;

    case RoyalMail = 1;

    public static function map(int $value): string
    {
        return match ($value) {
            self::RoyalMail->value => 'RoyalMail',
        };
    }
}
