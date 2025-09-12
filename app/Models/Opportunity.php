<?php

namespace App\Models;

use App\Enums\OpportunityStatus;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Translatable\HasTranslations;

class Opportunity extends Model
{
    use HasFactory, HasTranslations, BelongsToOrganization;

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
}
