<?php

namespace App\Enums;

// use Filament\Support\Contracts\HasColor;
// use Filament\Support\Contracts\HasIcon;
// use Filament\Support\Contracts\HasLabel;

enum Role: string // implements HasLabel, HasColor, HasIcon
{
    case individual = 'individual';
    // TODO: what if organizations may have multiple users. I don't believe an organization should be a role
    case organization = 'organization';
    case admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::individual => 'individual',
            self::organization => 'organization',
            self::admin => 'admin',
        };
    }

    public function getLabel(): ?string
    {
        return $this->label();
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::individual => 'heroicon-o-user',
            self::organization => 'heroicon-o-sparkles',
            self::admin => 'heroicon-o-shield-check',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::individual => 'primary',
            self::organization => 'info',
            self::admin => 'secondary',
        };
    }
}
