<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum FormFieldType: string implements HasLabel
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Date = 'date';
    case Select = 'select';
    case Checkbox = 'checkbox';
    case File = 'file';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Text Input',
            self::Textarea => 'Textarea',
            self::Date => 'Date Picker',
            self::Select => 'Dropdown Select',
            self::Checkbox => 'Checkbox',
            self::File => 'File Upload',
        };
    }

    public function getLabel(): ?string
    {
        return $this->label();
    }
}
