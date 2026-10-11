<?php
namespace App\Modules\PortalAlumno\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Modules\PortalAlumno\Services\PortalAlumnoService;
use App\Modules\Alumnos\Models\Alumno;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalAlumnoController extends Controller
{
    use ApiResponse;

    public function __construct(private PortalAlumnoService $service) {}

    private function getAuthAlumno(): Alumno
    {
        $alumno = request()->user()?->alumno;
        if (!$alumno) abort(403, 'Esta cuenta no está vinculada a un perfil de alumno. Contacta a control escolar.');
        return $alumno;
    }

    public function dashboard(): JsonResponse
    {
        return $this->success($this->service->dashboard($this->getAuthAlumno()));
    }

    public function perfil(): JsonResponse
    {
        return $this->success($this->service->perfil($this->getAuthAlumno()));
    }

    public function calificaciones(Request $request): JsonResponse
    {
        return $this->success($this->service->calificaciones($this->getAuthAlumno(), $request->get('periodo')));
    }

    public function kardex(): JsonResponse
    {
        return $this->success($this->service->kardex($this->getAuthAlumno()));
    }

    public function asistencias(Request $request): JsonResponse
    {
        return $this->success($this->service->asistencias($this->getAuthAlumno(), $request->all()));
    }

    public function horario(): JsonResponse
    {
        return $this->success($this->service->horario($this->getAuthAlumno()));
    }

    public function boleta(Request $request): JsonResponse
    {
        return $this->success($this->service->boleta($this->getAuthAlumno(), $request->get('periodo')));
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $user = request()->user();
        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return $this->error('La contraseña actual es incorrecta.', 422);
        }
        $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        $user->save();
        return $this->success(null, 'Contraseña actualizada.');
    }
}
