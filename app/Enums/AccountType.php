<?php

namespace App\Enums;

use App\Enums\Extras\Labels;
use App\Enums\Extras\Values;

enum AccountType: int
{
    use Labels, Values;

    case Free = 0;

    public static function map(int $value): string
    {
        return match ($value) {
            self::Free->value => 'Free',
        };
    }
}
