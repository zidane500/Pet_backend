<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLostFoundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'          => 'required|in:lost,found',
            'species'       => 'required|string|max:100',
            'breed'         => 'nullable|string|max:100',
            'name'          => 'nullable|string|max:100',
            'description'   => 'required|string|min:10|max:2000',
            'city'          => 'required|string|max:100',
            'address'       => 'nullable|string|max:255',
            'last_seen_at'  => 'nullable|date|before_or_equal:today',
            'contact_phone' => 'required|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'images'        => 'nullable|array|max:5',
            'images.*'      => 'string',
            'reward'        => 'nullable|numeric|min:0|max:99999',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'          => 'Le type (perdu/trouvé) est obligatoire.',
            'type.in'                => 'Le type doit être : lost ou found.',
            'species.required'       => 'L\'espèce est obligatoire.',
            'description.required'   => 'La description est obligatoire.',
            'description.min'        => 'La description doit contenir au moins 10 caractères.',
            'city.required'          => 'La ville est obligatoire.',
            'contact_phone.required' => 'Le téléphone de contact est obligatoire.',
            'last_seen_at.before_or_equal' => 'La date ne peut pas être dans le futur.',
            'images.max'             => 'Vous ne pouvez pas uploader plus de 5 images.',
        ];
    }
}