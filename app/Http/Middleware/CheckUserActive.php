<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Si pas connecté, on laisse passer (les routes publiques)
        if (!$user) {
            return $next($request);
        }

        // Compte désactivé par un admin
        if (!$user->is_active) {
            // Révoque tous les tokens pour forcer la déconnexion
            $user->tokens()->delete();

            return response()->json([
                'message' => 'Votre compte a été désactivé. Contactez le support.',
                'code'    => 'ACCOUNT_DISABLED',
            ], 403);
        }

        // Email non vérifié — avertissement mais pas bloquant
        // (décommenter si tu veux forcer la vérification email)
        // if (!$user->email_verified_at) {
        //     return response()->json([
        //         'message' => 'Veuillez vérifier votre email.',
        //         'code'    => 'EMAIL_NOT_VERIFIED',
        //     ], 403);
        // }

        return $next($request);
    }
}