<?php

namespace App\Models;

use App\Enums\ProgramStatus;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;
use CyrildeWit\EloquentViewable\InteractsWithViews;
use CyrildeWit\EloquentViewable\Contracts\Viewable;
use Laravel\Scout\Searchable;

class Program extends Model implements Viewable
{
    use HasFactory, HasTranslations, BelongsToOrganization, Searchable, InteractsWithViews;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'organization_id',
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
            'organization_id' => 'integer',
            'status' => ProgramStatus::class,
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->id,
            'title_en' => $this->getTranslation('title', 'en'),
            'title_ar' => $this->getTranslation('title', 'ar'),
            'description_en' => $this->getTranslation('description', 'en'),
            'description_ar' => $this->getTranslation('description', 'ar'),
            'created_at' => $this->created_at?->timestamp,
        ];
    }
}
