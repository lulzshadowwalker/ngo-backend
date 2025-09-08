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
use Laravel\Scout\Searchable;

#[ObservedBy(OrganizationObserver::class)]
class Organization extends Model
{
    use HasFactory, Searchable;

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

    public function organizationPreferences(): HasMany
    {
        return $this->hasMany(OrganizationPreference::class);
    }

    public function follows()
    {
        return $this->morphMany(Follow::class, 'followable');
    }

    /**
     * Check if the authenticated user is following this organization.
     */
    public function following(): Attribute
    {
        return Attribute::get(function (): bool {
            if (! Auth::check()) return false;

            return $this->follows()
                ->where('user_id', Auth::id())
                ->exists();
        });
    }

    public function toSearchableArray(): array
    {
        return [
            // TODO: Organization model should be localized
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
}
