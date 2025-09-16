<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\Role;
use App\Notifications\CustomResetPasswordNotification;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, InteractsWithMedia, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['name', 'email', 'password', 'organization_id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isIndividual(): Attribute
    {
        return Attribute::get(fn (): bool => $this->hasRole(Role::individual->value));
    }

    public function isOrganizer(): Attribute
    {
        return Attribute::get(fn (): bool => $this->hasRole(Role::organization->value));
    }

    public function isAdmin(): Attribute
    {
        return Attribute::get(fn (): bool => $this->hasRole(Role::admin->value));
    }

    public function scopeIndividuals(Builder $query): Builder
    {
        return $query->whereHas('roles', function (Builder $query) {
            $query->where('name', Role::individual->value);
        });
    }

    public function scopeOrganizations(Builder $query): Builder
    {
        return $query->whereHas('roles', function (Builder $query) {
            $query->where('name', Role::organization->value);
        });
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->whereHas('roles', function (Builder $query) {
            $query->where('name', Role::admin->value);
        });
    }

    public function individual()
    {
        return $this->hasOne(Individual::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    //  TODO: We need an organizer model and link the organizer model to the organization model

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function follows()
    {
        return $this->hasMany(Follow::class);
    }

    /**
     * Get organizations that this user is following (cleaner approach)
     */
    public function followedOrganizations()
    {
        return $this->hasManyThrough(
            Organization::class,
            Follow::class,
            'user_id',
            'id',
            'id',
            'followable_id'
        )->where('follows.followable_type', Organization::class);
    }

    /**
     * Follow an organization or individual
     */
    public function follow($followable)
    {
        return $this->follows()->firstOrCreate([
            'followable_id' => $followable->id,
            'followable_type' => get_class($followable),
        ]);
    }

    /**
     * Unfollow an organization or individual
     */
    public function unfollow($followable)
    {
        return $this->follows()
            ->where('followable_id', $followable->id)
            ->where('followable_type', get_class($followable))
            ->delete();
    }

    /**
     * Check if user is following a specific entity
     */
    public function isFollowing($followable): bool
    {
        return $this->follows()
            ->where('followable_id', $followable->id)
            ->where('followable_type', get_class($followable))
            ->exists();
    }

    public function preferences(): HasOne
    {
        return $this->hasOne(UserPreferences::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function routeNotificationForPush(Notification $notification): array
    {
        // TODO: Implement a method to retrieve device tokens for push notifications.
        return [];

        return $this->deviceTokens->pluck('token')->toArray();
    }

    const MEDIA_COLLECTION_AVATAR = 'avatar';

    public function registerMediaCollections(): void
    {
        $name = Str::replace(' ', '+', $this->name);

        $this->addMediaCollection(self::MEDIA_COLLECTION_AVATAR)
            ->singleFile()
            ->useFallbackUrl("https://ui-avatars.com/api/?name={$name}");
    }

    /**
     * Get the user's avatar URL.
     */
    public function avatar(): Attribute
    {
        return Attribute::get(
            fn () => $this->getFirstMediaUrl(self::MEDIA_COLLECTION_AVATAR) ?:
                null
        );
    }

    /**
     * Get the user's avatar file.
     */
    public function avatarFile(): Attribute
    {
        return Attribute::get(
            fn () => $this->getFirstMedia(self::MEDIA_COLLECTION_AVATAR) ?: null
        );
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->isAdmin;
        }

        if ($panel->getId() === 'cms') {
            return $this->isOrganizer;
        }

        return false;
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
