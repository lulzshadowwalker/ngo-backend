<?php

namespace App\Models;

use App\Enums\ProgramStatus;
use App\Observers\ProgramObserver;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;
use CyrildeWit\EloquentViewable\InteractsWithViews;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Laravel\Scout\Searchable;

#[ObservedBy(ProgramObserver::class)]
class Program extends Model implements HasMedia
{
    use HasFactory, HasTranslations, BelongsToOrganization, Searchable, InteractsWithMedia;

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

    const MEDIA_COLLECTION_COVER = 'cover';

    public function registerMediaCollections(): void
    {
        $fallback = 'https://placehold.co/400x225.png?text=' . str_replace(' ', '%20', $this->title);

        $this->addMediaCollection(self::MEDIA_COLLECTION_COVER)
            ->singleFile()
            ->useFallbackUrl($fallback);
    }

    /**
     * Get the cover URL.
     */
    public function cover(): Attribute
    {
        return Attribute::get(
            fn() => $this->getFirstMediaUrl(self::MEDIA_COLLECTION_COVER) ?:
                null
        );
    }

    /**
     * Get the cover file.
     */
    public function coverFile(): Attribute
    {
        return Attribute::get(
            fn() => $this->getFirstMedia(self::MEDIA_COLLECTION_COVER) ?: null
        );
    }
}
