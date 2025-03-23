<?php

namespace App\Enums\Extras;

use Illuminate\Support\Str;

trait Labels
{
    public static function labels(): array
    {
        $cases = static::cases();
        $options = [];
        foreach ($cases as $case) {
            $label = static::map($case->value);
            $options[$case->value] = Str::title($label);
        }

        return $options;
    }
}
