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

    public function label(): string
    {
        return match ($this) {
            self::Disabled => 'Disabled',
            self::Admin => 'Admin',
            self::Standard => 'Standard',
        };
    }
}
