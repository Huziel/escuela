<?php
namespace App\Modules\Grupos\Controllers;
use App\Http\Controllers\Controller; use App\Traits\ApiResponse;
use App\Modules\Grupos\Services\GrupoService;
use App\Modules\Grupos\Requests\GrupoRequest;
use App\Modules\Grupos\Resources\GrupoResource;
use App\Modules\Docentes\Resources\DocenteResource;
use App\Modules\Docentes\Models\Docente;
use App\Modules\Grupos\Models\Grupo;
use App\Modules\Horarios\Models\Horario;
use Illuminate\Http\JsonResponse; use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrupoController extends Controller
{
    use ApiResponse;
    public function __construct(private GrupoService $service) {}
    public function index(Request $request): JsonResponse {
        $hasFilter = $request->hasAny(['search','especialidad_id','semestre','turno','activo']);
        $data = $hasFilter
            ? $this->service->filtrar($request->all(), $request->get('per_page', 15))
            : $this->service->paginate($request->get('per_page', 15));
        return $this->paginated($data, 'Grupos obtenidos.');
    }
    public function store(GrupoRequest $request): JsonResponse { $g=$this->service->create($request->validated()); return $this->success(new GrupoResource($g->load(['especialidad','tutor.user'])),'Grupo creado.',201); }
    public function show(int $id): JsonResponse { $g=$this->service->findWithAlumnos($id); return $g?$this->success(new GrupoResource($g)):$this->error('No encontrado.',404); }
    public function update(GrupoRequest $request,int $id): JsonResponse { $g=$this->service->update($id,$request->validated()); return $this->success(new GrupoResource($g->load(['especialidad','tutor.user'])),'Actualizado.'); }
    public function destroy(int $id): JsonResponse { $this->service->delete($id); return $this->success(null,'Eliminado.'); }
    public function listAll(): JsonResponse { return $this->success($this->service->findAll(),'Grupos activos.'); }

    public function docentes(int $id): JsonResponse
    {
        $grupo = Grupo::with('docentes.user')->findOrFail($id);
        return $this->success(DocenteResource::collection($grupo->docentes), 'Docentes del grupo.');
    }

    public function asignarDocente(Request $request, int $id): JsonResponse
    {
        $request->validate(['docente_id' => 'required|exists:docentes,id']);
        $grupo = Grupo::findOrFail($id);
        $docenteId = $request->docente_id;

        $maxDocentes = (int) (DB::table('configuraciones')->where('clave', 'max_docentes_por_grupo')->value('valor') ?? 5);
        if ($grupo->docentes()->count() >= $maxDocentes) {
            return $this->error("Este grupo ya tiene el máximo de {$maxDocentes} docentes asignados.", 422);
        }

        if ($grupo->docentes()->where('docente_id', $docenteId)->exists()) {
            return $this->error('Este docente ya está asignado a este grupo.', 422);
        }

        $maxHoras = (int) (DB::table('configuraciones')->where('clave', 'max_horas_diarias')->value('valor') ?? 8);
        $horasExistentes = Horario::where('docente_id', $docenteId)
            ->where('grupo_id', $id)
            ->selectRaw("SUM(TIME_TO_SEC(TIMEDIFF(hora_fin, hora_inicio))) / 3600 as total_horas")
            ->groupBy('docente_id')
            ->value('total_horas') ?? 0;

        $grupo->docentes()->attach($docenteId);
        return $this->success(null, 'Docente asignado correctamente.');
    }

    public function removerDocente(int $id, int $docenteId): JsonResponse
    {
        $grupo = Grupo::findOrFail($id);
        $grupo->docentes()->detach($docenteId);
        return $this->success(null, 'Docente removido del grupo.');
    }

    public function verificarHorario(Request $request): JsonResponse
    {
        $request->validate([
            'docente_id' => 'required|exists:docentes,id',
            'grupo_id' => 'required|exists:grupos,id',
        ]);

        $maxHoras = (int) (DB::table('configuraciones')->where('clave', 'max_horas_diarias')->value('valor') ?? 8);
        $horas = Horario::where('docente_id', $request->docente_id)
            ->where('grupo_id', $request->grupo_id)
            ->select('dia', DB::raw("SUM(TIME_TO_SEC(TIMEDIFF(hora_fin, hora_inicio))) / 3600 as total_horas"))
            ->groupBy('dia')
            ->get();

        $alertas = [];
        foreach ($horas as $h) {
            if ($h->total_horas > $maxHoras) {
                $alertas[] = "El {$h->dia} excede {$maxHoras} horas (tiene {$h->total_horas}h).";
            }
        }

        return $this->success([
            'horas_por_dia' => $horas,
            'max_horas_permitidas' => $maxHoras,
            'alertas' => $alertas,
            'excede_limite' => count($alertas) > 0,
        ]);
    }
}
