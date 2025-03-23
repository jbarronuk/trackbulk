<?php

namespace App\Enums\Extras;

trait Values
{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
