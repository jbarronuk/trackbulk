<?php

namespace App\Enums\Extras;

trait Values
{
    /**
     * @return list<int|string>
     */
    public static function values(): array
    {
        return array_column(static::cases(), 'value');
    }
}
