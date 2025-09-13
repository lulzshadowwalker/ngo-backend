<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseFormRequest;

class SearchOpportunityRequest extends BaseFormRequest
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
        ];
    }
}
