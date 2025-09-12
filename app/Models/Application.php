<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_form_id',
        'user_id',
        'opportunity_id',
        'organization_id',
        'status',
        'submitted_at',
        'reviewed_at',
        'completed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'application_form_id' => 'integer',
            'user_id' => 'integer',
            'opportunity_id' => 'integer',
            'organization_id' => 'integer',
            'status' => ApplicationStatus::class,
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function applicationForm(): BelongsTo
    {
        return $this->belongsTo(ApplicationForm::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(ApplicationResponse::class);
    }
}
