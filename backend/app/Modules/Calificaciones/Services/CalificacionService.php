<?php
namespace App\Modules\Calificaciones\Services;
use App\Services\BaseService;
use App\Modules\Calificaciones\Repositories\CalificacionRepository;
use App\Modules\Calificaciones\Models\Calificacion;
use App\Modules\Alumnos\Models\Alumno;
use App\Modules\Grupos\Models\Grupo;
use App\Helpers\AuditoriaHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class CalificacionService extends BaseService
{
    public function __construct(protected CalificacionRepository $repository) { $this->repository = $repository; }
    public function filtrar(array $filters, int $perPage = 15) { return $this->repository->filtrar($filters,$perPage); }
    
    public function create(array $data): Model {
        $data['promedio_final'] = 0;
        $cal = $this->repository->create($data);
        $this->recalcular($cal);
        AuditoriaHelper::registrar('create','calificaciones',$cal->id,null,$cal->fresh()->toArray());
        return $cal->fresh();
    }

    public function update(int $id, array $data): Model {
        $ant = $this->repository->find($id)?->toArray();
        $cal = $this->repository->update($id, $data);
        $this->recalcular($cal);
        AuditoriaHelper::registrar('update','calificaciones',$id,$ant,$cal->fresh()->toArray());
        return $cal->fresh();
    }

    private function recalcular($cal): void {
        $prom = $this->repository->calcularPromedio($cal);
        $estatus = 'cursando';
        if ($prom >= 7) $estatus = 'aprobada';
        elseif ($prom > 0 && $prom < 7) $estatus = 'reprobada';
        $cal->update(['promedio_final' => $prom, 'estatus' => $estatus]);
    }

    public function delete(int $id): bool { $c=$this->repository->find($id); AuditoriaHelper::registrar('delete','calificaciones',$id,$c?->toArray()); return $this->repository->delete($id); }
    public function kardex(int $alumnoId): array { return $this->repository->kardexPorAlumno($alumnoId); }

    /**
     * Sincroniza las calificaciones vacías para un alumno basándose en:
     *  1. El plan de estudios de la especialidad del grupo (fuente autoritativa).
     *  2. Las materias del horario (schedule) del grupo (complemento).
     *
     * Por cada materia única resultante de la unión de ambas fuentes, crea un
     * registro de Calificacion con estado 'cursando' y parciales/asistencias nulos.
     * Usa firstOrCreate para no duplicar registros ya existentes.
     *
     * @param Alumno $alumno
     * @return int Número de calificaciones creadas (no las ya existentes)
     */
    public function sincronizarCalificacionesParaAlumno(Alumno $alumno): int
    {
        if (!$alumno->grupo_id) {
            Log::debug('CalificacionService::sincronizar — alumno sin grupo', [
                'alumno_id' => $alumno->id,
                'matricula' => $alumno->matricula,
            ]);
            return 0;
        }

        // Usar relación precargada si existe para evitar consulta extra (optimización batch)
        if ($alumno->relationLoaded('grupo') && $alumno->grupo) {
            $grupo = $alumno->grupo;
            if (!$grupo->relationLoaded('horarios')) {
                $grupo->load('horarios');
            }
        } else {
            $grupo = Grupo::with('horarios')->find($alumno->grupo_id);
        }

        if (!$grupo) {
            Log::warning('CalificacionService::sincronizar — grupo no encontrado', [
                'alumno_id' => $alumno->id,
                'grupo_id' => $alumno->grupo_id,
            ]);
            return 0;
        }

        // ── 1. Materias del horario (schedule) del grupo ──
        $materiasHorario = $grupo->horarios
            ->pluck('materia_id')
            ->unique()
            ->filter()
            ->values()
            ->toArray();

        // ── 2. Materias del plan de estudio (fuente autoritativa) ──
        $materiasPlan = [];
        if ($grupo->especialidad_id) {
            // Usar closure en el where para evitar que orWhere rompa
            // la consulta si en el futuro se agregan global scopes al modelo.
            $planes = \App\Models\PlanEstudio::where('activo', true)
                ->where(function ($query) use ($grupo) {
                    $query->where('especialidad_id', $grupo->especialidad_id)
                          ->orWhereNull('especialidad_id');
                })
                ->with(['materias' => function ($q) use ($grupo) {
                    if ($grupo->semestre) {
                        $q->wherePivot('semestre', $grupo->semestre);
                    }
                }])
                ->get();

            foreach ($planes as $plan) {
                foreach ($plan->materias as $m) {
                    $materiasPlan[] = $m->id;
                }
            }
        }

        // Si el plan tampoco aportó materias, usar tronco común global
        // (plan sin especialidad asignada) como último recurso.
        if (empty($materiasPlan) && empty($materiasHorario)) {
            $planesGlobales = \App\Models\PlanEstudio::where('activo', true)
                ->whereNull('especialidad_id')
                ->with(['materias' => function ($q) use ($grupo) {
                    if ($grupo->semestre) {
                        $q->wherePivot('semestre', $grupo->semestre);
                    }
                }])
                ->get();

            foreach ($planesGlobales as $plan) {
                foreach ($plan->materias as $m) {
                    $materiasPlan[] = $m->id;
                }
            }
        }

        // ── 3. Unir ambas fuentes y eliminar duplicados ──
        $materiasUnicas = collect(array_merge($materiasHorario, $materiasPlan))
            ->unique()
            ->filter()
            ->values();

        // Si después de todo no hay materias que sincronizar, salir sin error
        // (grupo recién creado sin plan de estudios ni horarios).
        if ($materiasUnicas->isEmpty()) {
            Log::info('CalificacionService::sincronizar — sin materias para sincronizar', [
                'alumno_id'    => $alumno->id,
                'matricula'    => $alumno->matricula,
                'grupo_id'     => $grupo->id,
                'grupo_nombre' => $grupo->nombre,
                'especialidad_id' => $grupo->especialidad_id,
                'materias_horario' => count($materiasHorario),
                'materias_plan'    => count($materiasPlan),
            ]);
            return 0;
        }

        // ── 4. Crear registros faltantes ──
        $creadas = 0;
        foreach ($materiasUnicas as $materiaId) {
            $cal = Calificacion::firstOrCreate(
                [
                    'alumno_id'  => $alumno->id,
                    'materia_id' => $materiaId,
                ],
                [
                    'grupo_id'       => $alumno->grupo_id,
                    'periodo'        => $alumno->ciclo_escolar ?? '',
                    'ciclo_escolar_id' => $alumno->ciclo_escolar_id,
                    'estatus'        => 'cursando',
                    'parcial1'       => null,
                    'parcial2'       => null,
                    'parcial3'       => null,
                    'asistencia_p1'  => null,
                    'asistencia_p2'  => null,
                    'asistencia_p3'  => null,
                ]
            );

            if ($cal->wasRecentlyCreated) {
                $creadas++;
            }
        }

        Log::info('CalificacionService::sincronizar — completado', [
            'alumno_id'            => $alumno->id,
            'matricula'            => $alumno->matricula,
            'grupo_id'             => $alumno->grupo_id,
            'materias_en_horario'  => count($materiasHorario),
            'materias_en_plan'     => count($materiasPlan),
            'materias_totales'     => $materiasUnicas->count(),
            'calificaciones_creadas' => $creadas,
        ]);

        return $creadas;
    }
}
