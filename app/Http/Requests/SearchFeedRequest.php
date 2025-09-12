<?php

namespace App\Http\Requests;

class SearchFeedRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'sometimes|string|in:organization,program,opportunity',
        ];
    }
}
