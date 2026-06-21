<?php

namespace App\Enums;

use App\Enums\Extras\Labels;
use App\Enums\Extras\Values;

enum AccountQuota: int
{
    use Labels, Values;

    case Free = 20;

    public function label(): string
    {
        return match ($this) {
            self::Free => 'Free',
        };
    }
}
