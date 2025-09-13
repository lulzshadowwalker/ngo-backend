<?php

namespace App\Models;

use App\Enums\ProfileVisibility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndividualPreference extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'profile_visibility',
        'individual_id',
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
            'individual_id' => 'integer',
            'profile_visibility' => ProfileVisibility::class,
        ];
    }

    public function individual(): BelongsTo
    {
        return $this->belongsTo(Individual::class);
    }
}
