<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitApi
{
    public function __construct(protected RateLimiter $limiter) {}

    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveKey($request);

        // 60 requêtes par minute pour les utilisateurs non connectés
        // 120 requêtes par minute pour les utilisateurs connectés
        $maxAttempts = $request->user() ? 120 : 60;

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = $this->limiter->availableIn($key);

            return response()->json([
                'message'     => 'Trop de requêtes. Réessayez dans ' . $retryAfter . ' secondes.',
                'retry_after' => $retryAfter,
            ], 429);
        }

        $this->limiter->hit($key, 60); // fenêtre de 60 secondes

        $response = $next($request);

        $remaining = max(0, $maxAttempts - $this->limiter->attempts($key));

        $response->headers->set('X-RateLimit-Limit',     $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', $remaining);

        return $response;
    }

    private function resolveKey(Request $request): string
    {
        // Clé unique par utilisateur connecté ou par IP
        return $request->user()
            ? 'api:user:' . $request->user()->id
            : 'api:ip:'   . $request->ip();
    }
}