<?php

namespace App\Models;

use App\Observers\PostObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Filters\QueryFilter;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Builder;
use CyrildeWit\EloquentViewable\InteractsWithViews;
use CyrildeWit\EloquentViewable\Contracts\Viewable;
use Laravel\Scout\Searchable;
use Spatie\Translatable\HasTranslations;

#[ObservedBy(PostObserver::class)]
class Post extends Model implements Viewable
{
    use HasFactory, InteractsWithViews, Searchable, BelongsToOrganization, HasTranslations;

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

    public $translatable = ["title", "content"];

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

    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->id,
            'title_en' => $this->getTranslation('title', 'en'),
            'title_ar' => $this->getTranslation('title', 'ar'),
            'slug' => $this->slug,
            'content_en' => $this->getTranslation('content', 'en'),
            'content_ar' => $this->getTranslation('content', 'ar'),
            'organization_id' => $this->organization_id,
            'sector_id' => $this->sector_id,
            'created_at' => $this->created_at?->timestamp,
        ];
    }
}
