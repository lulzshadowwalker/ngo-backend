<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseFormRequest;

class UpdateIndividualProfileRequest extends BaseFormRequest
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
            'data.attributes.avatar' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4048',
            'avatar' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4048',
            'data.attributes.bio' => 'sometimes|nullable|string|max:1000',
            'data.attributes.birthdate' => 'sometimes|nullable|date',
            'data.relationships.location.data.id' => 'sometimes|nullable|exists:locations,id',
            'data.relationships.skills.data' => 'sometimes|array',
            'data.relationships.skills.data.*.id' => 'required_with:data.relationships.skills.data|exists:skills,id',
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

    public function avatar()
    {
        return $this->file('data.attributes.avatar') ?: $this->file('avatar');
    }

    public function bio(): ?string
    {
        return $this->input('data.attributes.bio');
    }

    public function birthdate(): ?string
    {
        return $this->input('data.attributes.birthdate');
    }

    public function location(): ?int
    {
        return $this->input('data.relationships.location.data.id');
    }

    public function skills(): array
    {
        $skills = $this->input('data.relationships.skills.data', []);

        return collect($skills)->pluck('id')->toArray();
    }

    public function sectors(): array
    {
        $sectors = $this->input('data.relationships.sectors.data', []);

        return collect($sectors)->pluck('id')->toArray();
    }
}
