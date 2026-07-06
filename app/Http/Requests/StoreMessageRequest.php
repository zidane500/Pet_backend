<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        // On ne peut pas s'envoyer un message à soi-même
        return $this->user()->id !== (int) $this->input('receiver_id');
    }

    public function rules(): array
    {
        return [
            'receiver_id' => 'required|integer|exists:users,id',
            'body'        => 'required|string|min:1|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'receiver_id.required' => 'Le destinataire est obligatoire.',
            'receiver_id.exists'   => 'Cet utilisateur n\'existe pas.',
            'body.required'        => 'Le message ne peut pas être vide.',
            'body.max'             => 'Le message ne peut pas dépasser 2000 caractères.',
        ];
    }

    protected function failedAuthorization(): never
    {
        abort(403, 'Vous ne pouvez pas vous envoyer un message à vous-même.');
    }
}