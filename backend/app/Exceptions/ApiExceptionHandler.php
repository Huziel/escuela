<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiExceptionHandler extends ExceptionHandler
{
    protected function prepareJsonResponse($request, Throwable $e): JsonResponse
    {
        return response()->json($this->formatException($e), $this->getStatusCode($e));
    }

    private function formatException(Throwable $e): array
    {
        $response = [
            'success' => false,
            'message' => $this->getMessage($e),
        ];

        if ($e instanceof ValidationException) {
            $response['message'] = 'Error de validación.';
            $response['errors'] = $e->errors();
        }

        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
        }

        return $response;
    }

    private function getStatusCode(Throwable $e): int
    {
        return match (true) {
            $e instanceof ValidationException => 422,
            $e instanceof AuthenticationException => 401,
            $e instanceof AuthorizationException => 403,
            $e instanceof ModelNotFoundException,
            $e instanceof NotFoundHttpException => 404,
            $e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException => 429,
            default => method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500,
        };
    }

    private function getMessage(Throwable $e): string
    {
        return match (true) {
            $e instanceof AuthenticationException => 'No autenticado. Token inválido o expirado.',
            $e instanceof AuthorizationException => 'No tienes permisos para realizar esta acción.',
            $e instanceof ModelNotFoundException => 'Recurso no encontrado.',
            $e instanceof NotFoundHttpException => 'Ruta no encontrada.',
            $e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException => 'Demasiadas solicitudes. Intenta más tarde.',
            default => $e->getMessage() ?: 'Error interno del servidor.',
        };
    }
}
