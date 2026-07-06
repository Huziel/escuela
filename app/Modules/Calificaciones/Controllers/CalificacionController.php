<?php
namespace App\Modules\Calificaciones\Controllers;
use App\Http\Controllers\Controller; use App\Traits\ApiResponse;
use App\Modules\Calificaciones\Services\CalificacionService;
use App\Modules\Calificaciones\Requests\CalificacionRequest;
use App\Modules\Calificaciones\Resources\CalificacionResource;
use Illuminate\Http\JsonResponse; use Illuminate\Http\Request;

class CalificacionController extends Controller
{
    use ApiResponse;
    public function __construct(private CalificacionService $service) {}
    public function index(Request $request): JsonResponse { $data = $request->hasAny(['alumno_id','materia_id','grupo_id','periodo','estatus'])?$this->service->filtrar($request->all(),$request->get('per_page',15)):$this->service->paginate($request->get('per_page',15)); return $this->paginated($data,'Calificaciones obtenidas.'); }
    public function store(CalificacionRequest $request): JsonResponse { $c=$this->service->create($request->validated()); return $this->success(new CalificacionResource($c->load(['alumno.user','materia','grupo'])),'Creada.',201); }
    public function show(int $id): JsonResponse { $c=$this->service->find($id); return $c?$this->success(new CalificacionResource($c->load(['alumno.user','materia','grupo']))):$this->error('No encontrada.',404); }
    public function update(CalificacionRequest $request,int $id): JsonResponse { $c=$this->service->update($id,$request->validated()); return $this->success(new CalificacionResource($c->load(['alumno.user','materia','grupo'])),'Actualizada.'); }
    public function destroy(int $id): JsonResponse { $this->service->delete($id); return $this->success(null,'Eliminada.'); }
    public function kardex(int $alumnoId): JsonResponse { return $this->success($this->service->kardex($alumnoId),'Kardex.'); }
}
