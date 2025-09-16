<?php

namespace App\Observers;

use App\Models\Organization;
use Illuminate\Support\Str;

class OrganizationObserver
{
    /**
     * Handle the Organization "creating" event.
     *
     * @return void
     */
    public function creating(Organization $organization)
    {
        if (empty($organization->slug)) {
            $baseSlug = Str::of($organization->name)
                ->lower()
                ->replaceMatches('/[^a-z0-9]/i', '')
                ->__toString();
            $slug = $baseSlug;
            $i = 1;
            while (Organization::where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$i++;
            }
            $organization->slug = $slug;
        }
    }
}
