<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }
            $status = 500;
            $title = 'Internal Server Error';
            $detail = 'Terjadi kesalahan pada server.';

            if ($exception instanceof AuthenticationException) {
                $status = 401;
                $title = 'Unauthorized';
                $detail = 'Autentikasi diperlukan.';
            } elseif ($exception instanceof ValidationException) {
                $status = 422;
                $title = 'Unprocessable Content';
                $detail = 'Data yang dikirim tidak valid.';
            } elseif ($exception instanceof ModelNotFoundException || ($exception instanceof HttpExceptionInterface && $exception->getStatusCode() === 404)) {
                $status = 404;
                $title = 'Not Found';
                $detail = 'Resource yang diminta tidak ditemukan.';
            } elseif ($exception instanceof HttpExceptionInterface) {
                $status = $exception->getStatusCode();
                $title = $exception->getMessage() ?: $title;
                $detail = $exception->getMessage() ?: $detail;
            }

            $body = [
                'type' => 'about:blank',
                'title' => $title,
                'status' => $status,
                'detail' => $detail,
                'instance' => $request->getRequestUri(),
            ];
            if ($exception instanceof ValidationException) {
                $body['errors'] = $exception->errors();
            }

            return response()->json($body, $status, ['Content-Type' => 'application/problem+json']);
        });
    })->create();
