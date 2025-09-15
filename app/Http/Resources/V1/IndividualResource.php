<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IndividualResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'individual',
            'id' => (string) $this->id,
            'attributes' => [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->phone,
                'avatar' => $this->user->avatar,
                'bio' => $this->bio,
                'birthdate' => $this->birthdate,
            ],
            'includes' => [
                'location' => LocationResource::make($this->location),
                'skills' => SkillResource::collection($this->skills),
                'volunteeringInterests' => VolunteeringInterestResource::collection($this->volunteeringInterests),
                'applications' => ApplicationResource::collection($this->user->applications),
                'following' => OrganizationResource::collection($this->user->followedOrganizations),
            ],
        ];
    }
}
