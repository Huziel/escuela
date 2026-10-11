<?php
namespace App\Modules\Inscripciones\Controllers;
use App\Http\Controllers\Controller; use App\Traits\ApiResponse;
use App\Modules\Inscripciones\Services\InscripcionService;
use App\Modules\Inscripciones\Requests\InscripcionRequest;
use App\Modules\Inscripciones\Resources\InscripcionResource;
use Illuminate\Http\JsonResponse; use Illuminate\Http\Request;

class InscripcionController extends Controller
{
    use ApiResponse;
    public function __construct(private InscripcionService $service) {}
    public function index(Request $request): JsonResponse { $data = $request->hasAny(['alumno_id','grupo_id','tipo','estatus','periodo'])?$this->service->filtrar($request->all(),$request->get('per_page',15)):$this->service->paginate($request->get('per_page',15)); return $this->paginated($data,'Inscripciones obtenidas.'); }
    public function store(InscripcionRequest $request): JsonResponse { $data = $request->validated(); $materiaIds = $data['materia_ids'] ?? []; unset($data['materia_ids']); $i = $this->service->create($data); if (!empty($materiaIds)) $i->materias()->sync($materiaIds); return $this->success(new InscripcionResource($i->load(['alumno.user','grupo','materias'])),'Creada.',201); }
    public function show(int $id): JsonResponse { $i=$this->service->find($id); return $i?$this->success(new InscripcionResource($i->load(['alumno.user','grupo','materias']))):$this->error('No encontrada.',404); }
    public function update(InscripcionRequest $request,int $id): JsonResponse { $data = $request->validated(); $materiaIds = $data['materia_ids'] ?? []; unset($data['materia_ids']); $i = $this->service->update($id,$data); if (!empty($materiaIds)) $i->materias()->sync($materiaIds); return $this->success(new InscripcionResource($i->load(['alumno.user','grupo','materias'])),'Actualizada.'); }
    public function destroy(int $id): JsonResponse { $this->service->delete($id); return $this->success(null,'Eliminada.'); }
    public function aprobar(int $id): JsonResponse { return $this->success(new InscripcionResource($this->service->aprobar($id)->load(['alumno.user','grupo'])),'Aprobada.'); }
    public function rechazar(Request $request, int $id): JsonResponse { return $this->success(new InscripcionResource($this->service->rechazar($id,$request->observaciones)->load(['alumno.user','grupo'])),'Rechazada.'); }
}
