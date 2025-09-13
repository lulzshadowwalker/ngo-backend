<?php

namespace App\Http\Requests\V1;

use App\Http\Requests\BaseFormRequest;

class StorePostCommentRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data.relationships.comments.data.attributes.content' => 'required|string',
        ];
    }

    public function content(): string
    {
        return $this->input('data.relationships.comments.data.attributes.content');
    }
}
