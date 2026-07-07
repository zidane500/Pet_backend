<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Non authentifié.',
                'code'    => 'UNAUTHENTICATED',
            ], 401);
        }

        if ($user->role !== 'admin') {
            return response()->json([
                'message' => 'Accès refusé. Réservé aux administrateurs.',
                'code'    => 'FORBIDDEN',
            ], 403);
        }

        return $next($request);
    }
}