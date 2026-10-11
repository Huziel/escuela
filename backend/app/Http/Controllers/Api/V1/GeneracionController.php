<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Generacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeneracionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Generacion::withCount('alumnos');
        if ($request->has('activo')) {
            $query->where('activo', filter_var($request->activo, FILTER_VALIDATE_BOOLEAN));
        }
        $perPage = min((int) ($request->get('per_page', 15)), 100);
        return response()->json($query->latest()->paginate($perPage));
    }

    public function all(): JsonResponse
    {
        return response()->json(Generacion::where('activo', true)->orderBy('nombre')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:50|unique:generaciones',
            'fecha_inicio' => 'required|date',
            'fecha_termino' => 'required|date|after:fecha_inicio',
            'descripcion' => 'nullable|string|max:500',
            'activo' => 'boolean',
        ]);

        $generacion = Generacion::create($data);
        return response()->json($generacion, 201);
    }

    public function show(int $id): JsonResponse
    {
        $generacion = Generacion::withCount('alumnos')->findOrFail($id);
        return response()->json($generacion);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $generacion = Generacion::findOrFail($id);
        $data = $request->validate([
            'nombre' => 'nullable|string|max:50|unique:generaciones,nombre,'.$id,
            'fecha_inicio' => 'nullable|date',
            'fecha_termino' => 'nullable|date|after:fecha_inicio',
            'descripcion' => 'nullable|string|max:500',
            'activo' => 'boolean',
        ]);
        $generacion->update(array_filter($data, fn($v) => $v !== null));
        return response()->json($generacion);
    }

    public function destroy(int $id): JsonResponse
    {
        $generacion = Generacion::findOrFail($id);
        if ($generacion->alumnos()->exists()) {
            return response()->json(['message' => 'No se puede eliminar: tiene alumnos asignados.'], 422);
        }
        $generacion->delete();
        return response()->json(['message' => 'Generación eliminada.']);
    }
}
