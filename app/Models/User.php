<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Notifications\CustomResetPasswordNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Enums\Role;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Support\Str;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements HasMedia, FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles, InteractsWithMedia;


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ["name", "email", "password", "organization_id"];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ["password", "remember_token"];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            "email_verified_at" => "datetime",
            "password" => "hashed",
        ];
    }

    public function isIndividual(): Attribute
    {
        return Attribute::get(fn(): bool => $this->hasRole(Role::individual->value));
    }

    public function isOrganizer(): Attribute
    {
        return Attribute::get(fn(): bool => $this->hasRole(Role::organization->value));
    }

    public function isAdmin(): Attribute
    {
        return Attribute::get(fn(): bool => $this->hasRole(Role::admin->value));
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
        return $this->deviceTokens->pluck("token")->toArray();
    }

    const MEDIA_COLLECTION_AVATAR = 'avatar';

    public function registerMediaCollections(): void
    {
        $name = Str::replace(" ", "+", $this->name);

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
            fn() => $this->getFirstMediaUrl(self::MEDIA_COLLECTION_AVATAR) ?:
                null
        );
    }

    /**
     * Get the user's avatar file.
     */
    public function avatarFile(): Attribute
    {
        return Attribute::get(
            fn() => $this->getFirstMedia(self::MEDIA_COLLECTION_AVATAR) ?: null
        );
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->isAdmin;
        }

        if ($panel->getId() === 'organizer') {
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
}
