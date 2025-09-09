<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SectorResource extends JsonResource
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
            "type" => "sectors",
            "id" => (string) $this->id,
            "attributes" => [
                "name" => $this->name,
                "description" => $this->description,
                "createdAt" => $this->created_at->toIso8601String(),
                "updatedAt" => $this->updated_at->toIso8601String(),
            ],
            "relationships" => (object) [],
        ];
    }
}
