<?php
namespace App\Modules\Alumnos\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Modules\Alumnos\Services\AlumnoService;
use App\Modules\Alumnos\Requests\AlumnoRequest;
use App\Modules\Alumnos\Resources\AlumnoResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlumnoController extends Controller
{
    use ApiResponse;

    public function __construct(private AlumnoService $service) {}

    public function index(Request $request): JsonResponse
    {
        if ($request->hasAny(['search','especialidad_id','semestre','grupo_id','estatus'])) {
            $alumnos = $this->service->filtrar($request->all(), $request->get('per_page', 15));
        } else {
            $alumnos = $this->service->paginate($request->get('per_page', 15));
        }
        return $this->paginated($alumnos, 'Alumnos obtenidos correctamente.', AlumnoResource::class);
    }

    public function store(AlumnoRequest $request): JsonResponse
    {
        $alumno = $this->service->create($request->validated());
        return $this->success(new AlumnoResource($alumno->load(['user','especialidad','grupo','tutor'])), 'Alumno creado.', 201);
    }

    public function show(int $id): JsonResponse
    {
        $alumno = $this->service->find($id);
        if (!$alumno) {
            return $this->error('Alumno no encontrado.', 404);
        }
        $alumno->load(['user', 'especialidad', 'grupo', 'calificaciones.materia', 'asistencias', 'grupo.horarios.materia', 'grupo.horarios.docente.user']);
        return $this->success(new AlumnoResource($alumno), 'Alumno obtenido.');
    }

    public function update(AlumnoRequest $request, int $id): JsonResponse
    {
        $alumno = $this->service->update($id, $request->validated());
        return $this->success(new AlumnoResource($alumno->load(['user','especialidad','grupo','tutor'])), 'Alumno actualizado.');
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
}
