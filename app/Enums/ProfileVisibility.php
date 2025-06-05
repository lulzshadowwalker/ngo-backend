<?php

namespace App\Enums;

enum ProfileVisibility: string
{
    case public = "public";
    case private = "private";

    public static function values(): array
    {
        return array_map(fn($e) => $e->value, self::cases());
    }
}
