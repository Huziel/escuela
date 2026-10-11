<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Noticia;
use App\Modules\Alumnos\Models\Alumno;
use App\Modules\Docentes\Models\Docente;
use App\Modules\Grupos\Models\Grupo;
use App\Modules\Especialidades\Models\Especialidad;
use App\Modules\Materias\Models\Materia;
use App\Modules\Inscripciones\Models\Inscripcion;
use App\Modules\Calificaciones\Models\Calificacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total_alumnos' => Alumno::count(),
                'total_docentes' => Docente::count(),
                'total_grupos' => Grupo::count(),
                'total_materias' => Materia::count(),
                'total_especialidades' => Especialidad::count(),
                'total_inscripciones' => Inscripcion::count(),
                'total_egresados' => Alumno::where('estatus', 'egresado')->count(),
                'alumnos_activos' => Alumno::where('estatus', 'activo')->count(),
                'alumnos_baja' => Alumno::where('estatus', 'baja')->count(),
                'alumnos_bloqueados' => Alumno::where('estatus', 'bloqueado')->count(),
                'noticias' => Noticia::where('activo', true)->latest()->take(5)->get(),
                'total_usuarios' => User::count(),
            ],
        ]);
    }

    public function charts(): JsonResponse
    {
        $alumnosPorSemestre = Alumno::select('semestre', DB::raw('count(*) as total'))
            ->groupBy('semestre')->orderBy('semestre')->pluck('total', 'semestre');

        $alumnosPorEspecialidad = Alumno::select('especialidad_id', DB::raw('count(*) as total'))
            ->with('especialidad')->groupBy('especialidad_id')->get()
            ->map(fn($a) => ['label' => $a->especialidad?->nombre ?? 'N/A', 'total' => $a->total]);

        $calAprobadas = Calificacion::where('estatus', 'aprobada')->count();
        $calReprobadas = Calificacion::where('estatus', 'reprobada')->count();
        $calCursando = Calificacion::where('estatus', 'cursando')->count();

        $inscripcionesPorPeriodo = Inscripcion::select('periodo', DB::raw('count(*) as total'))
            ->groupBy('periodo')->orderBy('periodo')->pluck('total', 'periodo');

        $asistencia = DB::table('asistencias')
            ->select('estatus', DB::raw('count(*) as total'))
            ->groupBy('estatus')->pluck('total', 'estatus');

        return response()->json([
            'success' => true,
            'data' => [
                'alumnos_por_semestre' => $alumnosPorSemestre,
                'alumnos_por_especialidad' => $alumnosPorEspecialidad,
                'calificaciones' => [
                    'aprobadas' => $calAprobadas,
                    'reprobadas' => $calReprobadas,
                    'cursando' => $calCursando,
                ],
                'inscripciones_por_periodo' => $inscripcionesPorPeriodo,
                'asistencia' => $asistencia,
            ],
        ]);
    }
}
