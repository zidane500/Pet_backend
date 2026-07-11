<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ← Double sécurité : la route est déjà protégée par le
        // middleware IsAdmin, mais on revérifie ici aussi. Si jamais la
        // route change un jour et que quelqu'un oublie le middleware,
        // cette ligne empêche quand même un non-admin de créer un produit.
        return $this->user() !== null && $this->user()->role === 'admin';
    }

      public function rules(): array
    {
        return [
            'name'                => 'required|string|min:3|max:150',
            'description'         => 'nullable|string|max:2000',
            'category'            => 'required|in:chat,chien,oiseau,autre',
            'price'               => 'required|numeric|min:0|max:999999',
            'promotion_price'     => 'nullable|numeric|min:0|lt:price',
            'promotion_ends_at'   => 'nullable|date|after:now',
            'stock_quantity'      => 'required|integer|min:0|max:999999',
            'photos'              => 'nullable|array|max:6',
            'photos.*'            => 'string|url',
            'is_active'           => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                => 'Le nom du produit est obligatoire.',
            'name.min'                     => 'Le nom doit contenir au moins 3 caractères.',
            'category.required'            => 'La catégorie est obligatoire.',
            'category.in'                  => 'Catégorie invalide.',
            'price.required'               => 'Le prix est obligatoire.',
            'price.numeric'                => 'Le prix doit être un nombre.',
            'price.min'                    => 'Le prix ne peut pas être négatif.',
            'promotion_price.lt'           => 'Le prix promo doit être inférieur au prix normal.',
            'promotion_ends_at.after'      => 'La fin de promotion doit être dans le futur.',
            'stock_quantity.required'      => 'La quantité en stock est obligatoire.',
            'stock_quantity.integer'         => 'La quantité doit être un nombre entier.',
            'photos.max'                   => 'Maximum 6 photos par produit.',
        ];
    }
}