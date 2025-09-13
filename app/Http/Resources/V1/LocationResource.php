<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
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
            "type" => "locations",
            "id" => (string) $this->id,
            "attributes" => [
                "city" => $this->city,
                "country" => $this->country,
                "createdAt" => $this->created_at->toIso8601String(),
                "updatedAt" => $this->updated_at->toIso8601String(),
            ],
            "relationships" => [
                "individuals" => [
                    "data" => $this->whenLoaded("individuals", function () {
                        return $this->individuals
                            ->map(function ($individual) {
                                return [
                                    "type" => "individuals",
                                    "id" => (string) $individual->id,
                                ];
                            })
                            ->all();
                    }),
                ],
                "organizations" => [
                    "data" => $this->whenLoaded("organizations", function () {
                        return $this->organizations
                            ->map(function ($organization) {
                                return [
                                    "type" => "organizations",
                                    "id" => (string) $organization->id,
                                ];
                            })
                            ->all();
                    }),
                ],
            ],
        ];
    }
}
