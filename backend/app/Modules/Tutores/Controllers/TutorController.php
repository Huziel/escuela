<?php
namespace App\Modules\Tutores\Controllers;
use App\Http\Controllers\Controller; use App\Traits\ApiResponse;
use App\Modules\Tutores\Services\TutorService;
use App\Modules\Tutores\Requests\TutorRequest;
use App\Modules\Tutores\Resources\TutorResource;
use Illuminate\Http\JsonResponse; use Illuminate\Http\Request;

class TutorController extends Controller
{
    use ApiResponse;
    public function __construct(private TutorService $service) {}
    public function index(Request $request): JsonResponse { $data = $request->filled('search')?$this->service->filtrar($request->all(),$request->get('per_page',15)):$this->service->paginate($request->get('per_page',15)); return $this->paginated($data,'Tutores obtenidos.'); }
    public function store(TutorRequest $request): JsonResponse { $t=$this->service->create($request->validated()); return $this->success(new TutorResource($t->load(['user','alumnos.user'])),'Creado.',201); }
    public function show(int $id): JsonResponse { $t=$this->service->find($id); return $t?$this->success(new TutorResource($t->load(['user','alumnos.user']))):$this->error('No encontrado.',404); }
    public function update(TutorRequest $request,int $id): JsonResponse { $t=$this->service->update($id,$request->validated()); return $this->success(new TutorResource($t->load(['user','alumnos.user'])),'Actualizado.'); }
    public function destroy(int $id): JsonResponse { $this->service->delete($id); return $this->success(null,'Eliminado.'); }
    public function listAll(): JsonResponse { return $this->success($this->service->findAll(),'Tutores.'); }
}
