<?php

use App\Exceptions\InsufficientStockException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $error = fn (string $message, int $status, array $errors = []) => response()->json(array_filter([
            'success' => false,
            'message' => $message,
            'errors' => $errors ?: null,
        ], fn ($value) => $value !== null), $status);

        $exceptions->render(fn (InsufficientStockException $e) => $error(
            $e->getMessage(), 409, ['shortages' => $e->shortages],
        ));

        $exceptions->render(fn (ValidationException $e, Request $request) => $request->is('api/*')
            ? $error($e->getMessage(), 422, $e->errors())
            : null);

        $exceptions->render(fn (NotFoundHttpException $e, Request $request) => $request->is('api/*')
            ? $error($e->getPrevious() instanceof ModelNotFoundException ? 'Resource not found.' : 'Endpoint not found.', 404)
            : null);
    })->create();
