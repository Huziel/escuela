<?php namespace App\Traits;
use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function success($data = null, string $message = 'Operación exitosa', int $code = 200): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $code);
    }
    
    protected function error(string $message = 'Error', int $code = 400, $errors = null): JsonResponse
    {
        $response = ['success' => false, 'message' => $message];
        if ($errors) $response['errors'] = $errors;
        return response()->json($response, $code);
    }
    
    protected function paginated($paginator, string $message = 'Datos obtenidos correctamente', ?string $resourceClass = null): JsonResponse
    {
        if ($resourceClass) {
            $paginator = call_user_func([$resourceClass, 'collection'], $paginator);
        }
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
