<?php

namespace App\Enums;

use App\Enums\Extras\Labels;
use App\Enums\Extras\Values;

enum ProductStatus: int
{
    use Labels, Values;

    case Disabled = 0;
    case Active = 1;

    public function label(): string
    {
        return match ($this) {
            self::Disabled => 'Disabled',
            self::Active => 'Active',
        };
    }
}
