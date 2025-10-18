<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionRenderer
{
    /**
     * Register API exception handlers.
     */
    public static function register(Exceptions $exceptions): void
    {
        $respondsWithJson = function ($request): bool {
            return $request->expectsJson() || $request->wantsJson() || $request->is('api/*');
        };

        // Validation errors (422)
        $exceptions->renderable(function (ValidationException $e, $request) use ($respondsWithJson) {
            if (!$respondsWithJson($request)) {
                return null; // Let default render handle non-API
            }

            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422);
        });

        // Authentication (401)
        $exceptions->renderable(function (AuthenticationException $e, $request) use ($respondsWithJson) {
            if (!$respondsWithJson($request)) {
                return null;
            }

            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        });

        // Authorization (403)
        $exceptions->renderable(function (AuthorizationException $e, $request) use ($respondsWithJson) {
            if (!$respondsWithJson($request)) {
                return null;
            }

            return response()->json([
                'message' => 'This action is unauthorized.',
            ], 403);
        });

        // Model not found (404)
        $exceptions->renderable(function (ModelNotFoundException $e, $request) use ($respondsWithJson) {
            if (!$respondsWithJson($request)) {
                return null;
            }

            return response()->json([
                'message' => 'Resource not found.',
            ], 404);
        });

        // Route not found (404)
        $exceptions->renderable(function (NotFoundHttpException $e, $request) use ($respondsWithJson) {
            if (!$respondsWithJson($request)) {
                return null;
            }

            return response()->json([
                'message' => 'Endpoint not found.',
            ], 404);
        });

        // Method not allowed (405)
        $exceptions->renderable(function (MethodNotAllowedHttpException $e, $request) use ($respondsWithJson) {
            if (!$respondsWithJson($request)) {
                return null;
            }

            return response()->json([
                'message' => 'Method not allowed.',
            ], 405);
        });

        $exceptions->renderable(function (HttpException $e, $request) use ($respondsWithJson) {
            if (!$respondsWithJson($request)) {
                return null;
            }

            $status = $e->getStatusCode();
            $payload = [
                'message' => $e->getMessage() ?: 'HTTP Error',
            ];

            if (config('app.debug')) {
                $payload['code'] = $e->getCode();
            }

            return response()->json($payload, $status, $e->getHeaders());
        });

        // Fallback (500)
        $exceptions->renderable(function (Throwable $e, $request) use ($respondsWithJson) {
            if (!$respondsWithJson($request)) {
                return null;
            }

            $payload = [
                'message' => 'Server Error',
            ];

            if (config('app.debug')) {
                $payload['exception'] = class_basename($e);
                $payload['message'] = $e->getMessage();
                $payload['trace'] = collect($e->getTrace())->take(5);
            }

            return response()->json($payload, 500);
        });

        $exceptions->renderable(function (HttpResponseException $e) {
            return $e->getResponse();
        });
    }
}
