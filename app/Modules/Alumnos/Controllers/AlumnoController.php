<?php
namespace App\Modules\Alumnos\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Modules\Alumnos\Services\AlumnoService;
use App\Modules\Alumnos\Requests\AlumnoRequest;
use App\Modules\Alumnos\Resources\AlumnoResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Modules\Alumnos\Models\Alumno;
use Illuminate\Support\Facades\DB;

class AlumnoController extends Controller
{
    use ApiResponse;

    public function __construct(private AlumnoService $service) {}

    public function index(Request $request): JsonResponse
    {
        if ($request->hasAny(['search','especialidad_id','semestre','grupo_id','estatus','ciclo_escolar'])) {
            $alumnos = $this->service->filtrar($request->all(), $request->get('per_page', 15));
        } else {
            $alumnos = $this->service->paginate($request->get('per_page', 15));
        }
        return $this->paginated($alumnos, 'Alumnos obtenidos correctamente.', AlumnoResource::class);
    }

    public function store(AlumnoRequest $request): JsonResponse
    {
        $alumno = $this->service->create($request->validated());
        return $this->success(new AlumnoResource($alumno->load(['user','especialidad','grupo','tutor','generacion'])), 'Alumno creado.', 201);
    }

    public function show(int $id): JsonResponse
    {
        $alumno = $this->service->find($id);
        if (!$alumno) {
            return $this->error('Alumno no encontrado.', 404);
        }
        $alumno->load(['user', 'especialidad', 'grupo', 'generacion', 'calificaciones.materia', 'asistencias', 'grupo.horarios.materia', 'grupo.horarios.docente.user']);
        return $this->success(new AlumnoResource($alumno), 'Alumno obtenido.');
    }

    public function update(AlumnoRequest $request, int $id): JsonResponse
    {
        $alumno = $this->service->update($id, $request->validated());
        return $this->success(new AlumnoResource($alumno->load(['user','especialidad','grupo','tutor','generacion'])), 'Alumno actualizado.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->success(null, 'Alumno eliminado.');
    }

    public function bloquear(int $id): JsonResponse
    {
        $this->service->bloquear($id);
        return $this->success(null, 'Alumno bloqueado.');
    }

    public function desbloquear(int $id): JsonResponse
    {
        $this->service->desbloquear($id);
        return $this->success(null, 'Alumno desbloqueado.');
    }

    public function historial(int $id): JsonResponse
    {
        return $this->success($this->service->historialAcademico($id), 'Historial académico.');
    }

    public function promover(Request $request): JsonResponse
    {
        $periodo = $request->input('ciclo_escolar');
        $calService = app(\App\Modules\Calificaciones\Services\CalificacionService::class);

        $alumnos = Alumno::with(['calificaciones', 'user'])
            ->when($periodo, fn($q) => $q->where('ciclo_escolar', $periodo))
            ->where('estatus', '!=', 'egresado')
            ->get();

        // Sync calificaciones first for all students
        foreach ($alumnos as $a) {
            $calService->sincronizarCalificacionesParaAlumno($a);
        }
        // Reload with fresh calificaciones
        $alumnos->load('calificaciones');

        $promovidos = 0;
        $irregulares = 0;
        $egresados = 0;
        $detalle = [];

        foreach ($alumnos as $alumno) {
            $cals = $alumno->calificaciones;
            $total = $cals->count();
            if ($total === 0) continue;

            $reprobadas = $cals->filter(fn($c) => ($c->promedio_final ?? 0) > 0 && ($c->promedio_final ?? 0) < 7)->count();

            if ($reprobadas <= 2) {
                $nuevoSemestre = $alumno->semestre + 1;
                if ($nuevoSemestre > 6) {
                    $alumno->update(['estatus' => 'egresado']);
                    $egresados++;
                    $detalle[] = [
                        'alumno' => $alumno->user?->nombre_completo ?? $alumno->matricula,
                        'matricula' => $alumno->matricula,
                        'reprobadas' => $reprobadas,
                        'accion' => 'Egresado (6° semestre completado)',
                    ];
                    continue;
                }
                $nuevoPeriodo = $this->siguientePeriodo($alumno->ciclo_escolar);

                $alumno->update([
                    'semestre' => $nuevoSemestre,
                    'ciclo_escolar' => $nuevoPeriodo,
                    'estatus' => 'activo',
                ]);

                // Assign new grupo for the next semester
                $nuevoGrupo = \App\Modules\Grupos\Models\Grupo::where('especialidad_id', $alumno->especialidad_id)
                    ->where('semestre', $nuevoSemestre)
                    ->where('turno', $alumno->turno ?? $alumno->grupo?->turno)
                    ->where('ciclo_escolar', $nuevoPeriodo)
                    ->first();
                if ($nuevoGrupo) {
                    $alumno->update(['grupo_id' => $nuevoGrupo->id]);
                    $calService->sincronizarCalificacionesParaAlumno($alumno->fresh(['grupo.horarios']));
                }

                $promovidos++;
                $detalle[] = [
                    'alumno' => $alumno->user?->nombre_completo ?? $alumno->matricula,
                    'matricula' => $alumno->matricula,
                    'reprobadas' => $reprobadas,
                    'accion' => "Promovido a {$nuevoSemestre}° semestre, periodo {$nuevoPeriodo}",
                ];
            } else {
                $alumno->update(['estatus' => 'irregular']);
                $irregulares++;
                $detalle[] = [
                    'alumno' => $alumno->user?->nombre_completo ?? $alumno->matricula,
                    'matricula' => $alumno->matricula,
                    'reprobadas' => $reprobadas,
                    'accion' => "Irregular — {$reprobadas} materias reprobadas",
                ];
            }
        }

        return $this->success([
            'promovidos' => $promovidos,
            'irregulares' => $irregulares,
            'egresados' => $egresados,
            'total' => $promovidos + $irregulares + $egresados,
            'detalle' => $detalle,
        ], "Promoción completada. {$promovidos} promovidos, {$irregulares} irregulares, {$egresados} egresados.");
    }

    private function siguientePeriodo(string $periodo): string
    {
        $parts = explode('-', $periodo);
        $year = (int)($parts[0] ?? date('Y'));
        $sem = (int)($parts[1] ?? 1);

        if ($sem === 1) {
            return $year . '-2';
        } else {
            return ($year + 1) . '-1';
        }
    }
}
