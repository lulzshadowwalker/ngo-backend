<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ProfileVisibility: string implements HasLabel
{
    case Public = 'public';
    case Private = 'private';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Public => 'Public',
            self::Private => 'Private',
        };
    }

    public function getLabel(): ?string
    {
        return $this->label();
    }
}
