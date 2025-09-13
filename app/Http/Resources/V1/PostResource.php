<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
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
            "type" => "posts",
            "id" => (string) $this->id,
            "attributes" => [
                "title" => $this->title,
                "slug" => $this->slug,
                "cover" =>
                //  TODO: Remove posts placeholder cover image
                "https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=900&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MjB8fHZvbHVudGVlcnxlbnwwfHwwfHx8MA%3D%3D",
                "likeCount" => $this->likes()->count(),
                "commentCount" => $this->comments()->count(),
                "content" => $this->content,
                "createdAt" => $this->created_at->toIso8601String(),
                "createdAtReadable" => $this->created_at->diffForHumans(),
                "updatedAt" => $this->updated_at->toIso8601String(),
            ],
            "relationships" => [
                "organization" => [
                    "data" => [
                        "type" => "organizations",
                        "id" => (string) $this->organization_id,
                    ],
                ],
                "comments" => [
                    "data" => $this->whenLoaded("comments", function () {
                        return $this->comments
                            ->map(function ($comment) {
                                return [
                                    "type" => "comments",
                                    "id" => (string) $comment->id,
                                ];
                            })
                            ->all();
                    }),
                ],
                "likes" => (object) [
                    "data" => $this->whenLoaded("likes", function () {
                        return $this->likes
                            ->map(function ($like) {
                                return [
                                    "type" => "likes",
                                    "id" => (string) $like->id,
                                ];
                            })
                            ->all();
                    }),
                ],
            ],
            "includes" => [
                "organization" => new OrganizationResource(
                    $this->whenLoaded("organization")
                ),
                "comments" => CommentResource::collection(
                    $this->whenLoaded("comments")
                ),
                "likes" => LikeResource::collection($this->whenLoaded("likes")),
                "sector" => new SectorResource($this->whenLoaded("sector"))
            ],
        ];
    }
}
