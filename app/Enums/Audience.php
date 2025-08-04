<?php

namespace App\Enums;

// use Filament\Support\Contracts\HasColor;
// use Filament\Support\Contracts\HasIcon;
// use Filament\Support\Contracts\HasLabel;

enum Audience: string // implements HasLabel, HasColor, HasIcon
{
    case all = 'all';
    case individuals = 'individuals';
    case organizations = 'organizations';

    public function label(): string
    {
        return match ($this) {
            self::all => __('all'),
            self::individuals => __('individuals'),
            self::organizations => __('organizations'),
        };
    }

    public function getLabel(): ?string
    {
        return $this->label();
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::all => 'primary',
            self::individuals => 'info',
            self::organizations => 'secondary',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::all => 'heroicon-o-globe-alt',
            self::individuals => 'heroicon-o-users',
            self::organizations => 'heroicon-o-sparkles',
        };
    }
}
