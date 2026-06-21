<?php

namespace App\Enums\Extras;

use Illuminate\Support\Str;

trait Labels
{
    public static function labels(): array
    {
        $options = [];
        foreach (static::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    abstract public function label(): string;
}
