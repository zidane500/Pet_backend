<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // ─── API stateful (Sanctum) ───────────────────────────────────────
        $middleware->statefulApi();

        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // ─── Middlewares globaux ──────────────────────────────────────────
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->append(\App\Http\Middleware\RateLimitApi::class);

        // ─── Aliases ─────────────────────────────────────────────────────
        $middleware->alias([
            'auth.sanctum' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'active'       => \App\Http\Middleware\CheckUserActive::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {

        // ─── 401 Unauthenticated ──────────────────────────────────────────
        $exceptions->render(function (
            \Illuminate\Auth\AuthenticationException $e,
            $request
        ) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Non authentifié. Connectez-vous pour continuer.',
                    'code'    => 'UNAUTHENTICATED',
                ], 401);
            }
        });

        // ─── 422 Validation ───────────────────────────────────────────────
        $exceptions->render(function (
            \Illuminate\Validation\ValidationException $e,
            $request
        ) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Données invalides.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        // ─── 403 Forbidden ────────────────────────────────────────────────
        $exceptions->render(function (
            \Illuminate\Auth\Access\AuthorizationException $e,
            $request
        ) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Action non autorisée.',
                    'code'    => 'FORBIDDEN',
                ], 403);
            }
        });

        // ─── 404 Not Found ────────────────────────────────────────────────
        $exceptions->render(function (
            \Illuminate\Database\Eloquent\ModelNotFoundException $e,
            $request
        ) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Ressource introuvable.',
                    'code'    => 'NOT_FOUND',
                ], 404);
            }
        });

        // ─── 429 Too Many Requests ────────────────────────────────────────
        $exceptions->render(function (
            \Illuminate\Http\Exceptions\ThrottleRequestsException $e,
            $request
        ) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Trop de requêtes. Réessayez plus tard.',
                    'code'    => 'TOO_MANY_REQUESTS',
                ], 429);
            }
        });

        // ─── 500 Server Error ─────────────────────────────────────────────
        $exceptions->render(function (
            \Throwable $e,
            $request
        ) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $debug = config('app.debug');
                return response()->json([
                    'message' => 'Erreur serveur. Réessayez plus tard.',
                    'code'    => 'SERVER_ERROR',
                    'detail'  => $debug ? $e->getMessage() : null,
                ], 500);
            }
        });

    })->create();