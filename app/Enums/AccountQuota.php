<?php

namespace App\Enums;

use App\Enums\Extras\Labels;
use App\Enums\Extras\Values;

enum AccountQuota: int
{
    use Labels, Values;

    case Free = 20;

    public static function map(int $value): string
    {
        return match (self::from($value)) {
            self::Free => 'Free',
        };
    }
}
