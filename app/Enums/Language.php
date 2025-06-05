<?php

namespace App\Enums;

enum Language: string
{
    case en = "en";
    case ar = "ar";

    public static function values(): array
    {
        return array_map(fn($e) => $e->value, self::cases());
    }
}
