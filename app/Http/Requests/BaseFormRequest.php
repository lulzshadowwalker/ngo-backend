<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Collection;

class BaseFormRequest extends FormRequest
{
    protected function mapped(array $allowedAttributes = [], array $extraAttributes = []): Collection
    {
        $availabledAttributes = [];
        foreach ($allowedAttributes as $key => $value) {
            if ($this->has($key)) {
                $availabledAttributes[$value] = $this->input($key);
            }
        }

        $availabledAttributes = array_merge($availabledAttributes, $extraAttributes);

        return collect($availabledAttributes);
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'The given data was invalid.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
