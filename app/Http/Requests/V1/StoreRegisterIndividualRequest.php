<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseFormRequest;
use App\Rules\UniqueEmailRule;

class StoreRegisterIndividualRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data.attributes.name' => 'required|string|max:255',
            'data.attributes.email' => ['required', 'email', 'max:255', new UniqueEmailRule()],
            'data.attributes.bio' => 'nullable|string|max:1000',
            'data.attributes.phone' => 'nullable|string|max:20',
            'data.attributes.password' => 'required|string|min:8',
            'data.attributes.avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4048',
            'data.relationships.location.data.id' => 'nullable|exists:locations,id',
            'data.relationships.volunteeringInterests.data.*.id' => 'nullable|exists:volunteering_interests,id',
            'data.relationships.skills.data.*.id' => 'nullable|exists:skills,id',
        ];
    }

    public function name(): string
    {
        return $this->input('data.attributes.name');
    }

    public function email(): string
    {
        return $this->input('data.attributes.email');
    }

    public function password(): string
    {
        return $this->input('data.attributes.password');
    }

    public function avatar()
    {
        return $this->file('data.attributes.avatar');
    }

    public function location(): ?int
    {
        return $this->input('data.relationships.location.data.id');
    }

    public function bio(): ?string
    {
        return $this->input('data.attributes.bio');
    }

    public function birthdate(): ?string
    {
        return $this->input('data.attributes.birthdate');
    }

    public function phone(): ?string
    {
        return $this->input('data.attributes.phone');
    }

    public function volunteeringInterests(): array
    {
        return collect($this->input('data.relationships.volunteeringInterests.data', []))
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->toArray();
    }

    public function skills(): array
    {
        return collect($this->input('data.relationships.skills.data', []))
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->toArray();
    }
}
