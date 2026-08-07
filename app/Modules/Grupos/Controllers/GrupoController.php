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
use App\Modules\Alumnos\Models\Alumno;
use App\Modules\Alumnos\Resources\AlumnoResource;
use App\Modules\Calificaciones\Services\CalificacionService;
use Illuminate\Http\JsonResponse; use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrupoController extends Controller
{
    use ApiResponse;
    public function __construct(
        private GrupoService $service,
        private CalificacionService $calificacionService
    ) {}
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

    public function alumnos(int $id): JsonResponse
    {
        $grupo = Grupo::with('alumnos.user')->findOrFail($id);
        return $this->success(AlumnoResource::collection($grupo->alumnos), 'Alumnos del grupo.');
    }

    public function asignarAlumnos(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'alumno_ids' => 'required|array',
            'alumno_ids.*' => 'exists:alumnos,id',
        ]);

        $grupo = Grupo::with('horarios')->findOrFail($id);
        $alumnos = Alumno::whereIn('id', $request->alumno_ids)->get();

        foreach ($alumnos as $alumno) {
            if ($alumno->especialidad_id !== $grupo->especialidad_id) {
                return $this->error("El alumno {$alumno->matricula} no pertenece a la especialidad del grupo.", 422);
            }
            if ($alumno->semestre !== $grupo->semestre) {
                return $this->error("El alumno {$alumno->matricula} no coincide con el semestre del grupo.", 422);
            }
            if ($alumno->turno && $alumno->turno !== $grupo->turno) {
                return $this->error("El alumno {$alumno->matricula} tiene turno {$alumno->turno} y el grupo es {$grupo->turno}.", 422);
            }
            if ($grupo->alumnos()->count() >= $grupo->capacidad) {
                return $this->error("El grupo ya alcanzó su capacidad máxima ({$grupo->capacidad} alumnos).", 422);
            }
        }

        $asignados = 0;
        $calificacionesGeneradas = 0;
        foreach ($alumnos as $alumno) {
            if ($alumno->grupo_id !== $id) {
                $alumno->update(['grupo_id' => $id]);
                $asignados++;

                // Optimización: precargar relación grupo+horarios en el alumno para evitar consultas repetidas
                $alumno->setRelation('grupo', $grupo);
                $calificacionesGeneradas += $this->calificacionService->sincronizarCalificacionesParaAlumno($alumno);
            }
        }

        return $this->success([
            'asignados' => $asignados,
            'calificaciones_generadas' => $calificacionesGeneradas,
        ], "{$asignados} alumnos asignados al grupo.");
    }

    public function removerAlumno(int $id, int $alumnoId): JsonResponse
    {
        $alumno = Alumno::where('grupo_id', $id)->findOrFail($alumnoId);
        $alumno->update(['grupo_id' => null]);
        return $this->success(null, 'Alumno removido del grupo.');
    }
}
