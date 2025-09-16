<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class ApplicationForm extends Model
{
    use BelongsToOrganization, HasFactory, HasTranslations;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'opportunity_id',
        'organization_id',
        'title',
        'description',
        'is_active',
    ];

    /**
     * The translatable attributes.
     *
     * @var array
     */
    public $translatable = ['title', 'description'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'opportunity_id' => 'integer',
            'organization_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function formFields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
