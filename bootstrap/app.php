
<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
// use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Pastikan request API menghasilkan JSON.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e) => $request->is('api/*') || $request->expectsJson()
        );

        // Validasi gagal: HTTP 422.
        $exceptions->render(function (
            ValidationException $e,
            Request $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Validasi gagal.',
                    'errors' => $e->errors(),
                ], 422);
            }

            return null;
        });

        // Data atau endpoint tidak ditemukan: HTTP 404.
        $exceptions->render(function (
            NotFoundHttpException $e,
            Request $request
        ) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Data atau endpoint tidak ditemukan.',
                ], 404);
            }

            return null;
        });

        // Error API lainnya.
        $exceptions->render(function (
            Throwable $e,
            Request $request
        ) {
            if (! $request->is('api/*')) {
                return null;
            }

            // Biarkan Laravel menangani error yang sudah dikenali.
            if (
                $e instanceof ValidationException ||
                $e instanceof AuthenticationException ||
                $e instanceof AuthorizationException ||
                $e instanceof ModelNotFoundException ||
                $e instanceof HttpExceptionInterface
            ) {
                return null;
            }

            // Sembunyikan detail internal untuk error tak terduga.
            return response()->json([
                'message' => 'Terjadi kesalahan pada server.',
            ], 500);
        });
    })
    ->create();
