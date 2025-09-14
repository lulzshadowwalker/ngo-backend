<?php

namespace App\Models;

use App\Observers\OrganizationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

#[ObservedBy(OrganizationObserver::class)]
class Organization extends Model implements HasMedia
{
    use HasFactory, Searchable, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "name",
        "slug",
        "bio",
        "website",
        "contact_email",
        "sector_id",
        "location_id",
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
            'sector_id' => 'integer',
            'location_id' => 'integer',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function follows()
    {
        return $this->morphMany(Follow::class, 'followable');
    }

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

    /**
     * Check if the authenticated user is following this organization.
     */
    public function following(): Attribute
    {
        return Attribute::get(function (): bool {
            if (! Auth::guard('sanctum')->check()) return false;

            return $this->follows()
                ->where('user_id', Auth::guard('sanctum')->id())
                ->exists();
        });
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'bio' => $this->bio,
            'website' => $this->website,
            'created_at' => $this->created_at?->timestamp,
            'sector_id' => $this->sector_id,
            'location_id' => $this->location_id,
        ];
    }

    const MEDIA_COLLECTION_LOGO = 'logo';

    public function registerMediaCollections(): void
    {
        $name = Str::replace(" ", "+", $this->name);

        $this->addMediaCollection(self::MEDIA_COLLECTION_LOGO)
            ->singleFile()
            ->useFallbackUrl("https://ui-logos.com/api/?name={$name}");
    }

    /**
     * Get the logo URL.
     */
    public function logo(): Attribute
    {
        return Attribute::get(
            fn() => $this->getFirstMediaUrl(self::MEDIA_COLLECTION_LOGO) ?:
                null
        );
    }

    /**
     * Get the logo file.
     */
    public function logoFile(): Attribute
    {
        return Attribute::get(
            fn() => $this->getFirstMedia(self::MEDIA_COLLECTION_LOGO) ?: null
        );
    }
}
