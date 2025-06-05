<?php

namespace App\Enums;

enum Appearance: string
{
    case light = "light";
    case dark = "dark";
    case system = "system";

    public static function values(): array
    {
        return array_map(fn($e) => $e->value, self::cases());
    }
}
