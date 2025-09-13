<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'organization',
            'id' => (string) $this->id,
            'attributes' => [
                'name' => $this->name,
                'email' => $this->users->first()?->email,
                'logo' => $this->logo,
                'bio' => $this->bio,
                'website' => $this->website,
                'contactEmail' => $this->contact_email,
                'slug' => $this->slug,
            ],
            'includes' => [
                'location' => LocationResource::make($this->location),
                'sector' => SectorResource::make($this->sector),
            ],
        ];
    }
}
