<?php

namespace App\Http\Resources\V1;

use App\Services\TagParser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpportunityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'opportunities',
            'id' => (string) $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'isFeatured' => $this->is_featured,
            'organizationId' => $this->organization_id,
            'programId' => $this->program_id,
            'tags' => ! is_array($this->tags) ? (new TagParser)->parse($this->tags ?? '') : (array) $this->tags,
            'duration' => $this->duration,
            'expiryDate' => $this->expiry_date,
            'aboutTheRole' => $this->about_the_role,
            'keyResponsibilities' => $this->key_responsibilities,
            'requiredSkills' => $this->required_skills,
            'timeCommitment' => $this->time_commitment,
            'locationId' => $this->location_id,
            'latitude' => (string) $this->latitude,
            'longitude' => (string) $this->longitude,
            'locationDescription' => $this->location_description,
            'benefits' => $this->benefits,
            'sectorId' => $this->sector_id,
            'extra' => $this->extra,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,

            // Relationships
            'organization' => $this->whenLoaded('organization', function () {
                return [
                    'id' => $this->organization->id,
                    'name' => $this->organization->name,
                    'bio' => $this->whenNotNull($this->organization->bio),
                    'website' => $this->whenNotNull($this->organization->website),
                    'locationId' => $this->whenNotNull($this->organization->location_id),
                ];
            }),

            'program' => $this->whenLoaded('program', function () {
                return [
                    'id' => $this->program->id,
                    'title' => $this->program->title,
                    'description' => $this->whenNotNull($this->program->description),
                ];
            }),

            'sector' => $this->whenLoaded('sector', function () {
                return [
                    'id' => $this->sector->id,
                    'name' => $this->sector->name,
                ];
            }),

            'applicationForm' => $this->whenLoaded('applicationForm', function () {
                return [
                    'id' => $this->applicationForm->id,
                    'title' => $this->applicationForm->title,
                    'description' => $this->whenNotNull($this->applicationForm->description),
                    'formFields' => $this->applicationForm->relationLoaded('formFields')
                        ? $this->applicationForm->formFields->map(function ($field) {
                            return [
                                'id' => $field->id,
                                'type' => $field->type,
                                'label' => $field->label,
                                'placeholder' => $field->placeholder,
                                'helpText' => $field->help_text,
                                'isRequired' => $field->is_required,
                                'sortOrder' => $field->sort_order,
                                'options' => $field->options,
                                'validationRules' => $field->validation_rules,
                            ];
                        })
                        : null,
                ];
            }),
        ];
    }
}
