<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum OpportunityStatus: string implements HasColor, HasIcon, HasLabel
{
    case Active = 'active';
    case Closed = 'closed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Closed => 'Closed',
        };
    }

    public function getLabel(): ?string
    {
        return $this->label();
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active => 'success',
            self::Closed => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Active => 'heroicon-o-briefcase',
            self::Closed => 'heroicon-o-archive-box',
        };
    }
}
