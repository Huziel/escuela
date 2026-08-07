<?php
namespace App\Modules\PortalAlumno\Services;

use App\Modules\Alumnos\Models\Alumno;
use App\Modules\Calificaciones\Services\CalificacionService;
use App\Models\Aviso;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PortalAlumnoService
{
    public function __construct(private CalificacionService $calificacionService) {}

    public function dashboard(Alumno $alumno): array
    {
        $alumno->load(['user', 'especialidad', 'grupo', 'generacion', 'tutor.user']);
        $calificaciones = $alumno->calificaciones()->with('materia')->get();
        $total = $calificaciones->count();
        $aprobadas = $calificaciones->filter(fn($c) => ($c->promedio_final ?? 0) >= 7)->count();
        $reprobadas = $calificaciones->filter(fn($c) => ($c->promedio_final ?? 0) > 0 && ($c->promedio_final ?? 0) < 7)->count();
        $promedioGeneral = $total > 0 ? round($calificaciones->avg('promedio_final'), 1) : 0;

        $asistencias = $alumno->asistencias()->get();
        $totalAsist = $asistencias->count();
        $faltas = $asistencias->where('estatus', 'falta')->count();
        $porcentajeAsist = $totalAsist > 0 ? round((($totalAsist - $faltas) / $totalAsist) * 100, 1) : 100;

        $notificaciones = $alumno->user->notificaciones()->where('leida', false)->latest()->take(5)->get();
        $avisos = Aviso::where('activo', true)->latest()->take(5)->get();

        return [
            'alumno' => [
                'id' => $alumno->id, 'matricula' => $alumno->matricula,
                'nombre' => $alumno->user?->nombre_completo,
                'foto' => $alumno->user?->fotografia,
                'especialidad' => $alumno->especialidad?->nombre,
                'semestre' => $alumno->semestre,
                'grupo' => $alumno->grupo?->nombre,
                'turno' => $alumno->turno ?? $alumno->grupo?->turno,
                'estatus' => $alumno->estatus,
                'tutor' => $alumno->tutor?->user?->nombre_completo,
                'ciclo_escolar' => $alumno->ciclo_escolar,
            ],
            'resumen' => [
                'promedio_general' => $promedioGeneral,
                'materias_cursadas' => $total,
                'materias_aprobadas' => $aprobadas,
                'materias_reprobadas' => $reprobadas,
                'porcentaje_asistencia' => $porcentajeAsist,
                'faltas' => $faltas,
            ],
            'notificaciones' => $notificaciones,
            'avisos' => $avisos,
        ];
    }

    public function perfil(Alumno $alumno): array
    {
        $alumno->load(['user', 'especialidad', 'grupo', 'generacion', 'tutor.user']);
        return [
            'matricula' => $alumno->matricula,
            'curp' => $alumno->curp,
            'nombre' => $alumno->user?->nombre_completo,
            'email' => $alumno->user?->email,
            'telefono' => $alumno->user?->telefono,
            'direccion' => $alumno->user?->direccion,
            'foto' => $alumno->user?->fotografia,
            'especialidad' => $alumno->especialidad?->nombre,
            'semestre' => $alumno->semestre,
            'grupo' => $alumno->grupo?->nombre,
            'turno' => $alumno->turno ?? $alumno->grupo?->turno,
            'estatus' => $alumno->estatus,
            'ciclo_escolar' => $alumno->ciclo_escolar,
            'generacion' => $alumno->generacion?->nombre,
            'fecha_ingreso' => $alumno->fecha_ingreso?->format('Y-m-d'),
            'tutor' => $alumno->tutor?->user?->nombre_completo,
            'tutor_telefono' => $alumno->tutor?->user?->telefono,
            'tutor_parentesco' => $alumno->tutor?->parentesco,
        ];
    }

    public function updatePerfil(Alumno $alumno, array $data): array
    {
        if ($alumno->user) {
            $alumno->user->update($data);
        }
        return $this->perfil($alumno->fresh(['user', 'especialidad', 'grupo', 'tutor.user']));
    }

    public function updateFoto(Alumno $alumno, UploadedFile $file): array
    {
        $path = $file->store('fotos', 'public');
        if ($alumno->user) {
            if ($alumno->user->fotografia) Storage::disk('public')->delete($alumno->user->fotografia);
            $alumno->user->update(['fotografia' => $path]);
        }
        return $this->perfil($alumno->fresh(['user', 'especialidad', 'grupo', 'tutor.user']));
    }

    public function calificaciones(Alumno $alumno, ?string $periodo = null): array
    {
        $query = $alumno->calificaciones()->with('materia');
        if ($periodo) $query->where('periodo', $periodo);
        $calificaciones = $query->get();
        $total = $calificaciones->count();
        $promedio = $total > 0 ? round($calificaciones->avg('promedio_final'), 1) : 0;

        return [
            'promedio' => $promedio,
            'total' => $total,
            'periodos' => $alumno->calificaciones()->pluck('periodo')->unique()->values(),
            'calificaciones' => $calificaciones->groupBy('periodo')->map(fn($grupo) => $grupo->map(fn($c) => [
                'id' => $c->id, 'materia' => $c->materia?->nombre,
                'parcial1' => $c->parcial1, 'parcial2' => $c->parcial2, 'parcial3' => $c->parcial3,
                'asistencia_p1' => $c->asistencia_p1, 'asistencia_p2' => $c->asistencia_p2, 'asistencia_p3' => $c->asistencia_p3,
                'promedio_final' => $c->promedio_final, 'estatus' => $c->estatus,
            ])->values()),
        ];
    }

    public function kardex(Alumno $alumno): array
    {
        $alumno->load(['user', 'especialidad', 'generacion', 'calificaciones.materia', 'calificaciones.grupo']);
        $calificaciones = $alumno->calificaciones;
        $total = $calificaciones->count();
        $promedioGlobal = $total > 0 ? round($calificaciones->avg('promedio_final'), 1) : 0;
        $aprobadas = $calificaciones->filter(fn($c) => ($c->promedio_final ?? 0) >= 7)->count();

        return [
            'alumno' => ['nombre' => $alumno->user?->nombre_completo, 'matricula' => $alumno->matricula, 'especialidad' => $alumno->especialidad?->nombre],
            'estadisticas' => ['promedio_global' => $promedioGlobal, 'total_materias' => $total, 'aprobadas' => $aprobadas, 'reprobadas' => $total - $aprobadas],
            'historial' => $calificaciones->groupBy('periodo')->map(fn($grupo, $periodo) => [
                'periodo' => $periodo,
                'materias' => $grupo->map(fn($c) => [
                    'materia' => $c->materia?->nombre, 'grupo' => $c->grupo?->nombre,
                    'parcial1' => $c->parcial1, 'parcial2' => $c->parcial2, 'parcial3' => $c->parcial3,
                    'promedio_final' => $c->promedio_final, 'estatus' => $c->estatus,
                ])->values(),
                'promedio_periodo' => round($grupo->avg('promedio_final'), 1),
            ])->values(),
        ];
    }

    public function asistencias(Alumno $alumno, array $filters = []): array
    {
        $query = $alumno->asistencias()->with('materia');
        if (!empty($filters['fecha_desde'])) $query->where('fecha', '>=', $filters['fecha_desde']);
        if (!empty($filters['fecha_hasta'])) $query->where('fecha', '<=', $filters['fecha_hasta']);
        $asistencias = $query->latest('fecha')->paginate($filters['per_page'] ?? 20);
        $total = $asistencias->total();
        $faltas = $alumno->asistencias()->where('estatus', 'falta')->count();
        $pct = $total > 0 ? round((($total - $faltas) / $total) * 100, 1) : 100;

        return [
            'porcentaje' => $pct,
            'total' => $total,
            'faltas' => $faltas,
            'asistencias' => $asistencias,
        ];
    }

    public function horario(Alumno $alumno): array
    {
        $alumno->load(['grupo.horarios' => fn($q) => $q->with(['materia', 'docente.user'])->orderBy('hora_inicio')]);
        $horarios = $alumno->grupo?->horarios ?? collect();
        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];

        $schedule = [];
        foreach ($dias as $dia) {
            $schedule[$dia] = $horarios->where('dia', $dia)->map(fn($h) => [
                'id' => $h->id, 'hora_inicio' => $h->hora_inicio, 'hora_fin' => $h->hora_fin,
                'materia' => $h->materia?->nombre, 'docente' => $h->docente?->user?->nombre_completo,
                'aula' => $h->aula,
            ])->values();
        }

        return [
            'grupo' => $alumno->grupo?->nombre,
            'turno' => $alumno->grupo?->turno,
            'aula' => $alumno->grupo?->aula?->nombre,
            'horario' => $schedule,
        ];
    }

    public function boleta(Alumno $alumno, ?string $periodo = null): array
    {
        $query = $alumno->calificaciones()->with(['materia', 'grupo']);
        if ($periodo) $query->where('periodo', $periodo);
        $calificaciones = $query->get();
        $periodoActual = $periodo ?? $alumno->ciclo_escolar;

        return [
            'alumno' => ['nombre' => $alumno->user?->nombre_completo, 'matricula' => $alumno->matricula, 'grupo' => $alumno->grupo?->nombre, 'especialidad' => $alumno->especialidad?->nombre],
            'periodo' => $periodoActual,
            'materias' => $calificaciones->map(fn($c) => [
                'materia' => $c->materia?->nombre,
                'parcial1' => $c->parcial1, 'parcial2' => $c->parcial2, 'parcial3' => $c->parcial3,
                'promedio_final' => $c->promedio_final, 'estatus' => $c->estatus,
                'asistencia_p1' => $c->asistencia_p1, 'asistencia_p2' => $c->asistencia_p2, 'asistencia_p3' => $c->asistencia_p3,
            ]),
            'promedio_general' => $calificaciones->count() > 0 ? round($calificaciones->avg('promedio_final'), 1) : 0,
        ];
    }
}
