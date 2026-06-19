<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToggleFavoriteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'type' => is_string($this->input('type')) ? trim($this->input('type')) : $this->input('type'),
        ]);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(['listing', 'vet', 'pet_store'])],
            'id' => ['required', 'integer', 'min:1'],
        ];
    }
}
