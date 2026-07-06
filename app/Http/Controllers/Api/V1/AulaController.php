<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Aula;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AulaController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Aula::query();
        if ($request->filled('search')) {
            $query->where('nombre', 'like', '%' . $request->search . '%');
        }
        return $this->paginated($query->orderBy('nombre')->paginate($request->get('per_page', 50)), 'Aulas obtenidas.');
    }

    public function listAll(): JsonResponse
    {
        return $this->success(Aula::where('activo', true)->orderBy('nombre')->get(), 'Aulas activas.');
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:50|unique:aulas',
            'edificio' => 'nullable|string|max:50',
            'capacidad' => 'nullable|integer|min:1',
            'activo' => 'boolean',
        ]);
        $aula = Aula::create($data);
        return $this->success($aula, 'Aula creada.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $aula = Aula::findOrFail($id);
        $data = $request->validate([
            'nombre' => 'string|max:50|unique:aulas,nombre,' . $id,
            'edificio' => 'nullable|string|max:50',
            'capacidad' => 'nullable|integer|min:1',
            'activo' => 'boolean',
        ]);
        $aula->update($data);
        return $this->success($aula, 'Aula actualizada.');
    }

    public function destroy(int $id): JsonResponse
    {
        Aula::findOrFail($id)->delete();
        return $this->success(null, 'Aula eliminada.');
    }
}
