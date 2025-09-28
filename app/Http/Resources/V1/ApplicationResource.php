<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($request->routeIs('api.v1.profile.*')) {
            $this->load('opportunity.organization');
        }

        return [
            'type' => 'application',
            'id' => (string) $this->id,
            'attributes' => [
                'status' => $this->status,
                'submittedAt' => $this->submitted_at,
                'reviewedAt' => $this->reviewed_at,
                'completedAt' => $this->completed_at,
                'notes' => $this->notes,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
            'includes' => [
                'opportunity' => new OpportunityResource($this->opportunity),
                'organization' => new OrganizationResource($this->organization),
            ],
        ];
    }
}
