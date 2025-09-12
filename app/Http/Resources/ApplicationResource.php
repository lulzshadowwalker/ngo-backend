<?php

namespace App\Http\Resources;

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
        return [
            'id' => $this->id,
            'applicationFormId' => $this->application_form_id,
            'userId' => $this->user_id,
            'opportunityId' => $this->opportunity_id,
            'organizationId' => $this->organization_id,
            'status' => $this->status,
            'submittedAt' => $this->submitted_at,
            'reviewedAt' => $this->reviewed_at,
            'completedAt' => $this->completed_at,
            'notes' => $this->notes,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,

            // Relationships
            'opportunity' => $this->whenLoaded('opportunity', function () {
                return [
                    'id' => $this->opportunity->id,
                    'title' => $this->opportunity->title,
                    'description' => $this->opportunity->description,
                    'expiryDate' => $this->opportunity->expiry_date,
                ];
            }),

            'organization' => $this->whenLoaded('organization', function () {
                return [
                    'id' => $this->organization->id,
                    'name' => $this->organization->name,
                    'bio' => $this->organization->bio,
                ];
            }),

            'applicationForm' => $this->whenLoaded('applicationForm', function () {
                return [
                    'id' => $this->applicationForm->id,
                    'title' => $this->applicationForm->title,
                    'description' => $this->applicationForm->description,
                ];
            }),

            'responses' => $this->whenLoaded('responses', function () {
                return $this->responses->map(function ($response) {
                    return [
                        'id' => $response->id,
                        'formFieldId' => $response->form_field_id,
                        'value' => $response->value,
                        'filePath' => $response->file_path,
                        'formField' => $this->when($response->relationLoaded('formField'), function () use ($response) {
                            return [
                                'id' => $response->formField->id,
                                'type' => $response->formField->type,
                                'label' => $response->formField->label,
                                'isRequired' => $response->formField->is_required,
                            ];
                        }),
                    ];
                });
            }),
        ];
    }
}
