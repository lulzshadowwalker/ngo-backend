<?php

namespace App\Http\Resources\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            "type" => "comments",
            "id" => (string) $this->id,
            "attributes" => [
                "content" => $this->content,
                "createdAt" => $this->created_at->toIso8601String(),
                "createdAtReadable" => $this->created_at->diffForHumans(),
                "updatedAt" => $this->updated_at,
            ],
            "relationships" => [
                "user" => [
                    "data" => [
                        "type" => "users",
                        "id" => (string) $this->user_id,
                    ],
                ],
            ],
            "includes" => (object) [
                // "user" => new UserResource($this->whenLoaded('user')),
            ],
        ];
    }
}
