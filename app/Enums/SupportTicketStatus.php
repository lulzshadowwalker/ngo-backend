<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

// TODO: Rename enum cases to follow a consistent naming convention (lowercase)

enum SupportTicketStatus: string implements HasColor, HasIcon, HasLabel
{
    case Open = 'open';
    case InProgress = 'in-progress';
    case Resolved = 'resolved';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::InProgress => 'In Progress',
            self::Resolved => 'Resolved',
        };
    }

    public function getLabel(): ?string
    {
        return $this->label();
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Open => 'info',
            self::InProgress => 'warning',
            self::Resolved => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Open => 'heroicon-o-folder-open',
            self::InProgress => 'heroicon-o-arrow-path',
            self::Resolved => 'heroicon-o-check-circle',
        };
    }
}
