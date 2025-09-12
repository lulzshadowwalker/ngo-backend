<?php

namespace App\Traits;

/**
 * Adds global scope to where organization_id is the same as the authenticated user's organization_id.
 * given that 1. we have auth user 2. user->isOrganizer.
 * 
 * It applies to query scopes and when e.g. creating a new model
 */

trait BelongsToOrganization
{
    public static function bootBelongsToOrganization()
    {
        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->isOrganizer) {
                $model->organization_id = auth()->user()->organization->id;
            }
        });

        static::addGlobalScope('organization', function ($builder) {
            if (auth()->check() && auth()->user()->isOrganizer) {
                $builder->where('organization_id', auth()->user()->organization_id);
            }
        });
    }
}
