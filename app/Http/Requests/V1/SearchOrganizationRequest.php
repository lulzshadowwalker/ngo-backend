<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseFormRequest;

class SearchOrganizationRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sector' => 'sometimes|integer|exists:sectors,id',
            'location' => 'sometimes|integer|exists:locations,id',
        ];
    }
}
