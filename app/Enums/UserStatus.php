<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum UserStatus: string implements HasColor, HasIcon, HasLabel
{
    case active = 'active';
    case inactive = 'inactive';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::active => 'success',
            self::inactive => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::active => 'heroicon-o-check-circle',
            self::inactive => 'heroicon-o-x-circle',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::active => 'Active',
            self::inactive => 'Inactive',
        };
    }

    public function getLabel(): ?string
    {
        return $this->label();
    }
}
