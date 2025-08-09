<?php

namespace App\Http\Requests;

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
            'data.attributes.password' => 'required|string|min:8',
            'data.attributes.avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4048',
            'data.relationships.location.data.id' => 'nullable|exists:locations,id',
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
}
