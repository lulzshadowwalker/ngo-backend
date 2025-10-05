<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'type' => 'organizations',
            'id' => (string) $this->id,
            'attributes' => [
                'name' => $this->name,
                'slug' => $this->slug,
                'bio' => $this->bio,
                'logo' => $this->logo,
                'website' => $this->website,
                'sector' => $this->sector->name,
                'location' => $this->location->city.', '.$this->location->country,
                'following' => $this->following,
                'createdAt' => $this->created_at->toIso8601String(),
                'updatedAt' => $this->updated_at->toIso8601String(),
            ],
            'includes' => [
                'programs' => ProgramResource::collection($this->programs),
            ],
        ];
    }
}
