<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $content = $this->input('content');

        if (is_string($content)) {
            $this->merge([
                // Messages are treated as plain text. HTML is stripped server-side
                // and React renders it escaped on the client.
                'content' => trim(strip_tags($content)),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'receiver_id' => [
                'required',
                'integer',
                'exists:users,id',
                Rule::notIn([$this->user()?->id]),
            ],
            'content' => ['required', 'string', 'min:1', 'max:2000'],
            'listing_id' => ['nullable', 'integer', 'exists:listings,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'receiver_id.not_in' => 'Vous ne pouvez pas vous envoyer un message à vous-même.',
            'content.required' => 'Le message est obligatoire.',
            'content.max' => 'Le message ne peut pas dépasser 2000 caractères.',
        ];
    }
}
