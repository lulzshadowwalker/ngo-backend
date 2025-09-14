<?php

namespace App\Models;

use App\Enums\OpportunityStatus;
use App\Observers\OpportunityObserver;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Translatable\HasTranslations;
use CyrildeWit\EloquentViewable\InteractsWithViews;
use CyrildeWit\EloquentViewable\Contracts\Viewable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Laravel\Scout\Searchable;

#[ObservedBy(OpportunityObserver::class)]
class Opportunity extends Model implements Viewable, HasMedia
{
    use HasFactory, HasTranslations, BelongsToOrganization, Searchable, InteractsWithViews, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'is_featured',
        'organization_id',
        'program_id',
        'tags',
        'duration',
        'expiry_date',
        'about_the_role',
        'key_responsibilities',
        'required_skills',
        'time_commitment',
        'location_id',
        'latitude',
        'longitude',
        'location_description',
        'benefits',
        'sector_id',
        'extra',
    ];

    /**
     * The translatable attributes.
     *
     * @var array
     */
    public $translatable = [
        'title',
        'description',
        'tags',
        'about_the_role',
        'key_responsibilities',
        'required_skills',
        'time_commitment',
        'location_description',
        'benefits',
        'extra',
    ];

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
            'program_id' => 'integer',
            'location_id' => 'integer',
            'sector_id' => 'integer',
            'status' => OpportunityStatus::class,
            'is_featured' => 'boolean',
            'duration' => 'integer',
            'expiry_date' => 'date',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'tags' => 'array',
            'key_responsibilities' => 'array',
            'required_skills' => 'array',
            'time_commitment' => 'array',
            'benefits' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function applicationForm(): HasOne
    {
        return $this->hasOne(ApplicationForm::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status === OpportunityStatus::Active;
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->id,
            'title_en' => $this->getTranslation('title', 'en'),
            'title_ar' => $this->getTranslation('title', 'ar'),
            'description_en' => $this->getTranslation('description', 'en'),
            'description_ar' => $this->getTranslation('description', 'ar'),
            'organization_id' => $this->organization_id,
            'sector_id' => $this->sector_id,
            'tags_en' => $this->getTranslation('tags', 'en'),
            'tags_ar' => $this->getTranslation('tags', 'ar'),
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
