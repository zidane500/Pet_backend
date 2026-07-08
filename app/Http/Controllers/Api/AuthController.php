<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            ...$data,
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('petconnect')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email ou mot de passe incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Compte désactivé.'], 403);
        }

        // Une seule session active à la fois : déconnecte les anciennes
        $user->tokens()->delete();

        $token = $user->createToken('petconnect')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    /**
     * Déconnexion : supprime TOUS les tokens de l'utilisateur
     * → Le token ne peut plus être réutilisé, même s'il a été volé
     */
    public function logout(Request $request)
    {
        // Supprime TOUS les tokens de l'utilisateur (pas juste le current)
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Déconnecté avec succès. Tous les tokens ont été révoqués.'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->load('subscription'));
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return response()->json(['message' => __($status)]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
                // Supprime tous les tokens au reset du password
                $user->tokens()->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Mot de passe réinitialisé.']);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }
}