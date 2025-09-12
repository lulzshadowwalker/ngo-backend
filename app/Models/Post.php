<?php

namespace App\Models;

use App\Observers\PostObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use CyrildeWit\EloquentViewable\InteractsWithViews;
use CyrildeWit\EloquentViewable\Contracts\Viewable;

#[ObservedBy(PostObserver::class)]
class Post extends Model implements Viewable
{
    use HasFactory, InteractsWithViews;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ["title", "slug", "content", "organization_id", "sector_id"];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            "id" => "integer",
            "organization_id" => "integer",
        ];
    }

    /**
     * @return BelongsTo<Organization,Post>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return MorphMany<Comment,Post>
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, "commentable");
    }

    /**
     * @return MorphMany<Like,Post>
     */
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, "likeable");
    }

    /**
     * @param Builder<Model> $builder
     */
    public function scopeFilter(Builder $builder, QueryFilter $filters): Builder
    {
        return $filters->apply($builder);
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }
}
