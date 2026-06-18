<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|min:2|max:100',
            'email'    => 'required|email|unique:users,email|max:150',
            'password' => 'required|string|min:6|confirmed|max:100',
            'role'     => 'required|in:owner,vet,shop,shelter,breeder',
            'phone'    => 'nullable|string|max:20|regex:/^[+\d\s\-()]{7,20}$/',
            'city'     => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Le nom est obligatoire.',
            'name.min'           => 'Le nom doit contenir au moins 2 caractères.',
            'email.required'     => 'L\'email est obligatoire.',
            'email.email'        => 'L\'email est invalide.',
            'email.unique'       => 'Cet email est déjà utilisé.',
            'password.required'  => 'Le mot de passe est obligatoire.',
            'password.min'       => 'Le mot de passe doit contenir au moins 6 caractères.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'role.required'      => 'Le rôle est obligatoire.',
            'role.in'            => 'Rôle invalide.',
            'phone.regex'        => 'Le numéro de téléphone est invalide.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Données invalides.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name'  => strip_tags(trim($this->name ?? '')),
            'email' => strtolower(trim($this->email ?? '')),
        ]);
    }
}

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => 'required|email',
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'L\'email est obligatoire.',
            'email.email'       => 'L\'email est invalide.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Données invalides.',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim($this->email ?? '')),
        ]);
    }
}