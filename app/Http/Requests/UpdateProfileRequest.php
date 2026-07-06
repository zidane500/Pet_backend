<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name'                    => 'sometimes|string|min:2|max:255',
            'phone'                   => 'nullable|string|max:20',
            'city'                    => 'nullable|string|max:100',
            'bio'                     => 'nullable|string|max:500',
            'avatar'                  => 'nullable|string',
            'cover_image'             => 'nullable|string',

            // Changement de mot de passe
            'current_password'        => 'sometimes|required_with:password|string',
            'password'                => 'sometimes|string|min:8|confirmed',
            'password_confirmation'   => 'sometimes|string',

            // Préférences notifications (JSON)
            'notification_preferences'          => 'nullable|array',
            'notification_preferences.messages' => 'nullable|boolean',
            'notification_preferences.favorites'=> 'nullable|boolean',
            'notification_preferences.new_listings' => 'nullable|boolean',
            'notification_preferences.lost_found'   => 'nullable|boolean',
            'notification_preferences.promotions'   => 'nullable|boolean',

            // Préférences confidentialité (JSON)
            'privacy'                        => 'nullable|array',
            'privacy.profile_public'         => 'nullable|boolean',
            'privacy.show_phone'             => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.min'                     => 'Le nom doit contenir au moins 2 caractères.',
            'password.min'                 => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed'           => 'Les mots de passe ne correspondent pas.',
            'current_password.required_with' => 'Le mot de passe actuel est requis pour changer de mot de passe.',
        ];
    }
}