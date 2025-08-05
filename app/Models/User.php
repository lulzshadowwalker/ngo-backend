<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

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

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ["name", "email", "password"];

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

    public function isOrganization(): Attribute
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
}
