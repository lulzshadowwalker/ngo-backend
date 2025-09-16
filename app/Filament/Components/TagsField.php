<?php

namespace App\Filament\Components;

use App\Services\TagParser;
use Closure;
use Filament\Forms\Components\TagsInput;

class TagsField extends TagsInput
{
    protected TagParser $tagParser;

    public function __construct()
    {
        parent::__construct();
        $this->tagParser = new TagParser;

        // Set default configuration for tags
        $this->separator(',')
            ->splitKeys(['Tab', 'Enter'])
            ->placeholder('Enter tags separated by commas...')
            ->helperText('Enter comma-separated tags. Maximum '.$this->tagParser->getMaxTagsCount().' tags allowed.');
    }

    /**
     * Configure the field for translatable tag input with TagParser integration
     */
    public static function make(string $name): static
    {
        $field = parent::make($name);

        // Add custom validation for tags
        $field->rules([
            'array',
            'max:'.(new TagParser)->getMaxTagsCount(),
            function (string $attribute, $value, Closure $fail) {
                if (is_array($value)) {
                    $tagParser = new TagParser;
                    foreach ($value as $tag) {
                        if (mb_strlen($tag) > $tagParser->getMaxTagLength()) {
                            $fail("Each tag must be no longer than {$tagParser->getMaxTagLength()} characters.");
                        }
                    }
                }
            },
        ]);

        return $field;
    }

    /**
     * Parse comma-separated string into array for TagsInput
     */
    public function parseTagString(?string $input): array
    {
        if (! $input) {
            return [];
        }

        return $this->tagParser->parse($input);
    }

    /**
     * Convert array back to comma-separated string
     */
    public function tagsToString(array $tags): string
    {
        return implode(', ', array_filter($tags));
    }
}
