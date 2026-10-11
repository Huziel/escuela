<?php
namespace App\Modules\Regularizacion\Controllers;
use App\Http\Controllers\Controller; use App\Traits\ApiResponse;
use App\Modules\Regularizacion\Services\RegularizacionService;
use App\Modules\Regularizacion\Requests\RegularizacionRequest;
use App\Modules\Regularizacion\Resources\RegularizacionResource;
use Illuminate\Http\JsonResponse; use Illuminate\Http\Request;

class RegularizacionController extends Controller
{
    use ApiResponse;
    public function __construct(private RegularizacionService $service) {}
    public function index(Request $request): JsonResponse { $data = $request->hasAny(['alumno_id','materia_id','estatus'])?$this->service->filtrar($request->all(),$request->get('per_page',15)):$this->service->paginate($request->get('per_page',15)); return $this->paginated($data,'Regularizaciones obtenidas.'); }
    public function store(RegularizacionRequest $request): JsonResponse { $r=$this->service->create($request->validated()); return $this->success(new RegularizacionResource($r->load(['alumno.user','materia'])),'Creada.',201); }
    public function show(int $id): JsonResponse { $r=$this->service->find($id); return $r?$this->success(new RegularizacionResource($r->load(['alumno.user','materia']))):$this->error('No encontrada.',404); }
    public function update(RegularizacionRequest $request,int $id): JsonResponse { $r=$this->service->update($id,$request->validated()); return $this->success(new RegularizacionResource($r->load(['alumno.user','materia'])),'Actualizada.'); }
    public function destroy(int $id): JsonResponse { $this->service->delete($id); return $this->success(null,'Eliminada.'); }
}
