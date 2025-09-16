<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VolunteeringInterestResource extends JsonResource
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
            'type' => 'volunteering-interests',
            'id' => (string) $this->id,
            'attributes' => [
                'name' => $this->name,
                'createdAt' => $this->created_at->toIso8601String(),
                'updatedAt' => $this->updated_at->toIso8601String(),
            ],
            'relationships' => (object) [],
        ];
    }
}
