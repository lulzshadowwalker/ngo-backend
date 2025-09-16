<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseFormRequest;

class UpdateOrganizationProfileRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data.attributes.name' => 'sometimes|string|max:255',
            'data.attributes.email' => 'sometimes|email|max:255|unique:users,email,'.$this->user()->id,
            'data.attributes.logo' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4048',
            'logo' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4048',
            'data.attributes.bio' => 'sometimes|nullable|string|max:1000',
            'data.attributes.website' => 'sometimes|nullable|url',
            'data.attributes.contactEmail' => 'sometimes|nullable|email',
            'data.relationships.location.data.id' => 'sometimes|nullable|exists:locations,id',
            'data.relationships.sector.data.id' => 'sometimes|nullable|exists:sectors,id',
        ];
    }

    public function name(): ?string
    {
        return $this->input('data.attributes.name');
    }

    public function email(): ?string
    {
        return $this->input('data.attributes.email');
    }

    public function logo()
    {
        return $this->file('data.attributes.logo') ?: $this->file('logo');
    }

    public function bio(): ?string
    {
        return $this->input('data.attributes.bio');
    }

    public function website(): ?string
    {
        return $this->input('data.attributes.website');
    }

    public function contactEmail(): ?string
    {
        return $this->input('data.attributes.contactEmail');
    }

    public function location(): ?int
    {
        return $this->input('data.relationships.location.data.id');
    }

    public function sector(): ?int
    {
        return $this->input('data.relationships.sector.data.id');
    }
}
