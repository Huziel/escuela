<?php
namespace App\Modules\Materias\Controllers;
use App\Http\Controllers\Controller; use App\Traits\ApiResponse;
use App\Modules\Materias\Services\MateriaService;
use App\Modules\Materias\Requests\MateriaRequest;
use App\Modules\Materias\Resources\MateriaResource;
use Illuminate\Http\JsonResponse; use Illuminate\Http\Request;

class MateriaController extends Controller
{
    use ApiResponse;
    public function __construct(private MateriaService $service) {}
    public function index(Request $request): JsonResponse { $data = $request->hasAny(['search','especialidad_id','semestre','activo'])?$this->service->filtrar($request->all(),$request->get('per_page',15)):$this->service->paginate($request->get('per_page',15)); return $this->paginated($data,'Materias obtenidas.'); }
    public function store(MateriaRequest $request): JsonResponse { $m=$this->service->create($request->validated()); return $this->success(new MateriaResource($m->load('especialidad')),'Creada.',201); }
    public function show(int $id): JsonResponse { $m=$this->service->find($id); return $m?$this->success(new MateriaResource($m->load('especialidad'))):$this->error('No encontrada.',404); }
    public function update(MateriaRequest $request,int $id): JsonResponse { $m=$this->service->update($id,$request->validated()); return $this->success(new MateriaResource($m->load('especialidad')),'Actualizada.'); }
    public function destroy(int $id): JsonResponse { $this->service->delete($id); return $this->success(null,'Eliminada.'); }
    public function listAll(): JsonResponse { return $this->success($this->service->findAll(),'Materias activas.'); }
}
