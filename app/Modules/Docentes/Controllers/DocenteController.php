<?php
namespace App\Modules\Docentes\Controllers;
use App\Http\Controllers\Controller; use App\Traits\ApiResponse;
use App\Modules\Docentes\Services\DocenteService;
use App\Modules\Docentes\Requests\DocenteRequest;
use App\Modules\Docentes\Resources\DocenteResource;
use Illuminate\Http\JsonResponse; use Illuminate\Http\Request;

class DocenteController extends Controller
{
    use ApiResponse;
    public function __construct(private DocenteService $service) {}
    public function index(Request $request): JsonResponse {
        $data = $request->hasAny(['search','estatus']) ? $this->service->filtrar($request->all(), $request->get('per_page',15)) : $this->service->paginate($request->get('per_page',15));
        return $this->paginated($data, 'Docentes obtenidos.', DocenteResource::class);
    }
    public function store(DocenteRequest $request): JsonResponse { $d=$this->service->create($request->validated()); return $this->success(new DocenteResource($d->load(['user','especialidadRel','materias','grupos'])),'Docente creado.',201); }
    public function show(int $id): JsonResponse {
        $d = $this->service->find($id);
        if (!$d) return $this->error('No encontrado.', 404);
        $d->load(['user', 'especialidadRel', 'materias', 'grupos.alumnos.user', 'horarios.materia', 'horarios.grupo']);
        return $this->success(new DocenteResource($d));
    }
    public function update(DocenteRequest $request,int $id): JsonResponse { $d=$this->service->update($id,$request->validated()); return $this->success(new DocenteResource($d->load(['user','especialidadRel','materias','grupos'])),'Actualizado.'); }
    public function destroy(int $id): JsonResponse { $this->service->delete($id); return $this->success(null,'Eliminado.'); }
    public function listAll(): JsonResponse { return $this->success(DocenteResource::collection($this->service->findAll()), 'Docentes activos.'); }
}
