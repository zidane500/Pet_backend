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
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        [
            'prefix' => 'api',
            'middleware' => ['api', 'auth:sanctum', 'active', 'throttle:60,1'],
        ],
    )
    ->withMiddleware(function (Middleware $middleware) {

        // ─── API stateful Sanctum ─────────────────────────────────────────
        $middleware->statefulApi();

        // ─── Middlewares globaux ──────────────────────────────────────────
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // Attention : garde RateLimitApi seulement si tu sais qu’il ne double pas
        // inutilement les throttles déjà définis sur routes/api.php.
        $middleware->append(\App\Http\Middleware\RateLimitApi::class);

        // ─── Aliases ─────────────────────────────────────────────────────
        $middleware->alias([
            'active' => \App\Http\Middleware\CheckUserActive::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (
            \Illuminate\Auth\AuthenticationException $e,
            $request
        ) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Non authentifié. Connectez-vous pour continuer.',
                    'code' => 'UNAUTHENTICATED',
                ], 401);
            }
        });

        $exceptions->render(function (
            \Illuminate\Validation\ValidationException $e,
            $request
        ) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Données invalides.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (
            \Illuminate\Auth\Access\AuthorizationException $e,
            $request
        ) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Action non autorisée.',
                    'code' => 'FORBIDDEN',
                ], 403);
            }
        });

        $exceptions->render(function (
            \Illuminate\Database\Eloquent\ModelNotFoundException $e,
            $request
        ) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Ressource introuvable.',
                    'code' => 'NOT_FOUND',
                ], 404);
            }
        });

        $exceptions->render(function (
            \Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e,
            $request
        ) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Route introuvable.',
                    'code' => 'ROUTE_NOT_FOUND',
                ], 404);
            }
        });

        $exceptions->render(function (
            \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e,
            $request
        ) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Méthode HTTP non autorisée.',
                    'code' => 'METHOD_NOT_ALLOWED',
                ], 405);
            }
        });

        $exceptions->render(function (
            \Illuminate\Http\Exceptions\ThrottleRequestsException $e,
            $request
        ) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Trop de requêtes. Réessayez plus tard.',
                    'code' => 'TOO_MANY_REQUESTS',
                ], 429);
            }
        });

        $exceptions->render(function (
            \Throwable $e,
            $request
        ) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Erreur serveur. Réessayez plus tard.',
                    'code' => 'SERVER_ERROR',
                    'detail' => config('app.debug') ? $e->getMessage() : null,
                ], 500);
            }
        });

    })->create();