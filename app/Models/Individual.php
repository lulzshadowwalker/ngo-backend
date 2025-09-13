<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Individual extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ["bio", "birthdate", "location_id", "user_id"];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            "id" => "integer",
            "location_id" => "integer",
            "user_id" => "integer",
            "birthdate" => "date",
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'individual_skill', 'individual_id', 'skill_id')
            ->withTimestamps();
    }

    public function volunteeringInterests(): HasMany
    {
        return $this->hasMany(VolunteeringInterest::class);
    }

    public function individualPreferences(): HasOne
    {
        return $this->hasOne(IndividualPreference::class);
    }

    /**
     * Calculate profile completion percentage based on key fields.
     *
     * @return Attribute<int, never>
     */
    public function profileCompletion(): Attribute
    {
        return Attribute::get(function (): int {
            $completionPoints = 0;
            $totalPoints = 3;

            if (!empty($this->bio)) {
                $completionPoints++;
            }

            if ($this->location_id !== null) {
                $completionPoints++;
            }

            if ($this->skills()->exists()) {
                $completionPoints++;
            }

            return (int) round(($completionPoints / $totalPoints) * 100);
        });
    }
}
