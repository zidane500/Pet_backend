<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name'            => 'sometimes|required|string|min:3|max:150',
            'description'     => 'nullable|string|max:2000',
            'category'        => 'sometimes|required|in:chat,chien,oiseau,autre',
            'price'           => 'sometimes|required|numeric|min:0|max:999999',
            'stock_quantity'  => 'sometimes|required|integer|min:0|max:999999',
            'photos'          => 'nullable|array|max:6',
            'photos.*'        => 'string|url',
            'is_active'       => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'           => 'Le nom du produit est obligatoire.',
            'name.min'                => 'Le nom doit contenir au moins 3 caractères.',
            'category.in'             => 'Catégorie invalide.',
            'price.numeric'           => 'Le prix doit être un nombre.',
            'price.min'               => 'Le prix ne peut pas être négatif.',
            'stock_quantity.integer'  => 'La quantité doit être un nombre entier.',
            'photos.max'              => 'Maximum 6 photos par produit.',
        ];
    }
}