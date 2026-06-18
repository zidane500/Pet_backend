<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title'         => 'required|string|min:5|max:200',
            'type'          => 'required|in:adoption,vente,perdu,trouve,accouplement,conseils',
            'species'       => 'required|string|max:50',
            'breed'         => 'nullable|string|max:100',
            'price'         => 'nullable|numeric|min:0|max:999999',
            'is_free'       => 'boolean',
            'city'          => 'nullable|string|max:100',
            'region'        => 'nullable|string|max:100',
            'description'   => 'nullable|string|max:2000',
            'photos'        => 'nullable|array|max:5',
            'photos.*'      => 'string|url',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:100',
            'is_vaccinated' => 'boolean',
            'is_sterilized' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'   => 'Le titre est obligatoire.',
            'title.min'        => 'Le titre doit contenir au moins 5 caractères.',
            'title.max'        => 'Le titre ne peut pas dépasser 200 caractères.',
            'type.required'    => 'Le type d\'annonce est obligatoire.',
            'type.in'          => 'Type d\'annonce invalide.',
            'species.required' => 'L\'espèce est obligatoire.',
            'price.numeric'    => 'Le prix doit être un nombre.',
            'price.min'        => 'Le prix ne peut pas être négatif.',
            'photos.max'       => 'Maximum 5 photos autorisées.',
            'photos.*.url'     => 'Les URLs des photos sont invalides.',
            'contact_email.email' => 'L\'email de contact est invalide.',
        ];
    }

    // Retourne toujours du JSON même si pas de header Accept: application/json
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Données invalides.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }

    // Nettoie les données avant validation
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_free'       => $this->boolean('is_free'),
            'is_vaccinated' => $this->boolean('is_vaccinated'),
            'is_sterilized' => $this->boolean('is_sterilized'),
            'title'         => strip_tags($this->title ?? ''),
            'description'   => strip_tags($this->description ?? ''),
        ]);
    }
}