<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Models\PlanEstudio;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanEstudioController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = PlanEstudio::with(['especialidad', 'materias']);
        if ($request->filled('especialidad_id')) $query->where('especialidad_id', $request->especialidad_id);
        return $this->success($query->orderBy('nombre')->get(), 'Planes de estudio.');
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'especialidad_id' => 'required|exists:especialidades,id',
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);
        $plan = PlanEstudio::create($data);
        return $this->success($plan->load(['especialidad', 'materias']), 'Plan creado.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $plan = PlanEstudio::findOrFail($id);
        $data = $request->validate([
            'especialidad_id' => 'exists:especialidades,id',
            'nombre' => 'string|max:100',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);
        $plan->update($data);
        return $this->success($plan->load(['especialidad', 'materias']), 'Actualizado.');
    }

    public function destroy(int $id): JsonResponse
    {
        PlanEstudio::findOrFail($id)->delete();
        return $this->success(null, 'Eliminado.');
    }

    public function syncMaterias(Request $request, int $id): JsonResponse
    {
        $request->validate(['materias' => 'array']);
        $plan = PlanEstudio::findOrFail($id);
        $sync = [];
        foreach ($request->materias as $m) {
            $sync[$m['id']] = ['semestre' => $m['semestre'] ?? null, 'horas' => $m['horas'] ?? null];
        }
        $plan->materias()->sync($sync);
        return $this->success($plan->load('materias'), 'Materias sincronizadas.');
    }
}
