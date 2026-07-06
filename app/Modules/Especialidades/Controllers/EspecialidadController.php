<?php
namespace App\Modules\Especialidades\Controllers;
use App\Http\Controllers\Controller; use App\Traits\ApiResponse;
use App\Modules\Especialidades\Services\EspecialidadService;
use App\Modules\Especialidades\Requests\EspecialidadRequest;
use App\Modules\Especialidades\Resources\EspecialidadResource;
use Illuminate\Http\JsonResponse; use Illuminate\Http\Request;

class EspecialidadController extends Controller
{
    use ApiResponse;
    public function __construct(private EspecialidadService $service) {}
    public function index(Request $request): JsonResponse { $data = $request->filled('search')||$request->has('activo') ? $this->service->filtrar($request->all(), $request->get('per_page',15)) : $this->service->paginate($request->get('per_page',15)); return $this->paginated($data,'Especialidades obtenidas.'); }
    public function store(EspecialidadRequest $request): JsonResponse { $e = $this->service->create($request->validated()); return $this->success(new EspecialidadResource($e),'Creada.',201); }
    public function show(int $id): JsonResponse { $e = $this->service->find($id); return $e?$this->success(new EspecialidadResource($e)):$this->error('No encontrada.',404); }
    public function update(EspecialidadRequest $request, int $id): JsonResponse { $e = $this->service->update($id,$request->validated()); return $this->success(new EspecialidadResource($e),'Actualizada.'); }
    public function destroy(int $id): JsonResponse { $this->service->delete($id); return $this->success(null,'Eliminada.'); }
    public function listAll(): JsonResponse { return $this->success($this->service->findAll(),'Especialidades activas.'); }
}
