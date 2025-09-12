<?php

namespace App\Models;

use App\Enums\FormFieldType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class FormField extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'application_form_id',
        'type',
        'label',
        'placeholder',
        'help_text',
        'validation_rules',
        'options',
        'is_required',
        'sort_order',
    ];

    public $translatable = ['label', 'placeholder', 'help_text', 'options'];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'application_form_id' => 'integer',
            'type' => FormFieldType::class,
            'validation_rules' => 'array',
            'options' => 'array',
            'is_required' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function applicationForm(): BelongsTo
    {
        return $this->belongsTo(ApplicationForm::class);
    }
}
