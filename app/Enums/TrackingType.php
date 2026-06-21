<?php

namespace App\Enums;

use App\Enums\Extras\Labels;
use App\Enums\Extras\Values;

enum TrackingType: int
{
    use Labels, Values;

    case RoyalMail = 1;

    public function label(): string
    {
        return match ($this) {
            self::RoyalMail => 'RoyalMail',
        };
    }
}
