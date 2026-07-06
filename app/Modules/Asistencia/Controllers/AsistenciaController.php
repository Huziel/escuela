<?php
namespace App\Modules\Asistencia\Controllers;
use App\Http\Controllers\Controller; use App\Traits\ApiResponse;
use App\Modules\Asistencia\Services\AsistenciaService;
use App\Modules\Asistencia\Requests\AsistenciaRequest;
use App\Modules\Asistencia\Resources\AsistenciaResource;
use Illuminate\Http\JsonResponse; use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    use ApiResponse;
    public function __construct(private AsistenciaService $service) {}
    public function index(Request $request): JsonResponse { $data = $request->hasAny(['alumno_id','grupo_id','materia_id','fecha','estatus'])?$this->service->filtrar($request->all(),$request->get('per_page',15)):$this->service->paginate($request->get('per_page',15)); return $this->paginated($data,'Asistencias obtenidas.'); }
    public function store(AsistenciaRequest $request): JsonResponse { $a=$this->service->create($request->validated()); return $this->success(new AsistenciaResource($a->load(['alumno.user','materia','grupo'])),'Registrada.',201); }
    public function show(int $id): JsonResponse { $a=$this->service->find($id); return $a?$this->success(new AsistenciaResource($a->load(['alumno.user','materia','grupo']))):$this->error('No encontrada.',404); }
    public function update(AsistenciaRequest $request,int $id): JsonResponse { $a=$this->service->update($id,$request->validated()); return $this->success(new AsistenciaResource($a->load(['alumno.user','materia','grupo'])),'Actualizada.'); }
    public function destroy(int $id): JsonResponse { $this->service->delete($id); return $this->success(null,'Eliminada.'); }
    public function paseLista(Request $request): JsonResponse { $request->validate(['grupo_id'=>'required|exists:grupos,id','materia_id'=>'required|exists:materias,id','fecha'=>'required|date','asistencias'=>'required|array','asistencias.*.alumno_id'=>'required|exists:alumnos,id','asistencias.*.estatus'=>'required|in:asistencia,retardo,falta']); $this->service->registrarLote($request->all()); return $this->success(null,'Pase de lista registrado.'); }
    public function justificar(Request $request,int $id): JsonResponse { $request->validate(['justificacion'=>'required|string|max:500']); return $this->success(new AsistenciaResource($this->service->justificar($id,$request->justificacion)->load(['alumno.user','materia','grupo'])),'Justificada.'); }
}
