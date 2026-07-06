<?php
namespace App\Modules\Reportes\Controllers;
use App\Http\Controllers\Controller; use App\Traits\ApiResponse;
use App\Modules\Alumnos\Models\Alumno; use App\Modules\Docentes\Models\Docente;
use App\Modules\Grupos\Models\Grupo; use App\Modules\Especialidades\Models\Especialidad;
use App\Modules\Materias\Models\Materia; use App\Modules\Calificaciones\Models\Calificacion;
use App\Modules\Inscripciones\Models\Inscripcion;
use Illuminate\Http\JsonResponse; use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{
    use ApiResponse;

    public function kardex(Request $request): JsonResponse
    {
        $request->validate(['alumno_id' => 'required|exists:alumnos,id']);
        $alumno = Alumno::with(['user','especialidad','calificaciones.materia','calificaciones.grupo'])->findOrFail($request->alumno_id);
        return $this->success($alumno, 'Kardex generado.');
    }

    public function kardexPdf(Request $request)
    {
        $alumno = Alumno::with(['user','especialidad','calificaciones.materia','calificaciones.grupo'])->findOrFail($request->alumno_id);
        $pdf = Pdf::loadView('reportes.kardex', compact('alumno'));
        return $pdf->download("kardex_{$alumno->matricula}.pdf");
    }

    public function boleta(Request $request)
    {
        $request->validate(['alumno_id' => 'required|exists:alumnos,id', 'periodo' => 'required|string']);
        $alumno = Alumno::with('user')->findOrFail($request->alumno_id);
        $calificaciones = Calificacion::with('materia')->where('alumno_id',$request->alumno_id)->where('periodo',$request->periodo)->get();
        $pdf = Pdf::loadView('reportes.boleta', compact('alumno','calificaciones'));
        return $pdf->download("boleta_{$alumno->matricula}_{$request->periodo}.pdf");
    }

    public function asistencia(Request $request)
    {
        $request->validate(['grupo_id' => 'required|exists:grupos,id', 'fecha_desde' => 'required|date', 'fecha_hasta' => 'required|date|after_or_equal:fecha_desde']);
        $asistencias = Calificacion::where('grupo_id',$request->grupo_id); // simplified
        return $this->success([], 'Reporte de asistencia.');
    }

    public function indicadores(Request $request): JsonResponse
    {
        return $this->success([
            'total_alumnos' => Alumno::count(),
            'total_docentes' => Docente::count(),
            'total_grupos' => Grupo::count(),
            'total_especialidades' => Especialidad::count(),
            'total_materias' => Materia::count(),
            'total_inscripciones' => Inscripcion::count(),
            'alumnos_activos' => Alumno::where('estatus','activo')->count(),
            'alumnos_egresados' => Alumno::where('estatus','egresado')->count(),
            'indice_aprobacion' => Calificacion::count() > 0 ? round((Calificacion::where('estatus','aprobada')->count() / Calificacion::count()) * 100, 2) : 0,
            'indice_reprobacion' => Calificacion::count() > 0 ? round((Calificacion::where('estatus','reprobada')->count() / Calificacion::count()) * 100, 2) : 0,
        ], 'Indicadores.');
    }
}
