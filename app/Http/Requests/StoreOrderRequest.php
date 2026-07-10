<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ← Il faut juste être connecté (pas besoin d'être admin ici,
        // n'importe quel utilisateur peut commander).
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'items'              => 'required|array|min:1|max:30',
            'items.*.product_id' => 'required|integer|exists:products,id',
            // ← On vérifie juste que le produit existe ici. La
            // vérification du STOCK DISPONIBLE se fait dans le
            // contrôleur, dans une transaction avec verrouillage de
            // ligne (lockForUpdate), pour éviter qu'une vente en trop
            // se produise si deux clients commandent le même produit au
            // même moment.
            'items.*.quantity'   => 'required|integer|min:1|max:50',

            'shipping_name'      => 'required|string|min:2|max:150',
            'shipping_phone'     => 'required|string|min:8|max:20',
            'shipping_address'   => 'required|string|min:5|max:255',
            'shipping_city'      => 'required|string|max:100',
            'notes'              => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'              => 'Le panier est vide.',
            'items.min'                   => 'Le panier est vide.',
            'items.*.product_id.exists'   => 'Un des produits du panier n\'existe plus.',
            'items.*.quantity.min'        => 'La quantité doit être d\'au moins 1.',
            'shipping_name.required'      => 'Le nom est obligatoire.',
            'shipping_phone.required'     => 'Le numéro de téléphone est obligatoire.',
            'shipping_phone.min'          => 'Le numéro de téléphone semble incomplet.',
            'shipping_address.required'   => 'L\'adresse de livraison est obligatoire.',
            'shipping_city.required'      => 'La ville est obligatoire.',
        ];
    }
}