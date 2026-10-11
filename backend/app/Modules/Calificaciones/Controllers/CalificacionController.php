<?php
namespace App\Modules\Calificaciones\Controllers;
use App\Http\Controllers\Controller; use App\Traits\ApiResponse;
use App\Modules\Calificaciones\Services\CalificacionService;
use App\Modules\Calificaciones\Requests\CalificacionRequest;
use App\Modules\Calificaciones\Resources\CalificacionResource;
use App\Modules\Calificaciones\Models\Calificacion;
use App\Modules\Alumnos\Models\Alumno;
use Illuminate\Http\JsonResponse; use Illuminate\Http\Request;

class CalificacionController extends Controller
{
    use ApiResponse;
    public function __construct(private CalificacionService $service) {}
    public function index(Request $request): JsonResponse { $data = $request->hasAny(['alumno_id','materia_id','grupo_id','periodo','estatus'])?$this->service->filtrar($request->all(),$request->get('per_page',15)):$this->service->paginate($request->get('per_page',15)); return $this->paginated($data,'Calificaciones obtenidas.'); }
    public function store(CalificacionRequest $request): JsonResponse { $c=$this->service->create($request->validated()); return $this->success(new CalificacionResource($c->load(['alumno.user','materia','grupo'])),'Creada.',201); }
    public function show(int $id): JsonResponse { $c=$this->service->find($id); return $c?$this->success(new CalificacionResource($c->load(['alumno.user','materia','grupo']))):$this->error('No encontrada.',404); }
    public function update(CalificacionRequest $request,int $id): JsonResponse { $c=$this->service->update($id,$request->validated()); return $this->success(new CalificacionResource($c->load(['alumno.user','materia','grupo'])),'Actualizada.'); }
    public function destroy(int $id): JsonResponse { $this->service->delete($id); return $this->success(null,'Eliminada.'); }
    public function kardex(int $alumnoId): JsonResponse { return $this->success($this->service->kardex($alumnoId),'Kardex.'); }

    public function grupoMateria(int $grupoId, int $materiaId): JsonResponse
    {
        $alumnos = Alumno::where('grupo_id', $grupoId)->with('user')->get();
        $existingCals = Calificacion::whereIn('alumno_id', $alumnos->pluck('id'))
            ->where('materia_id', $materiaId)
            ->where('grupo_id', $grupoId)
            ->get()
            ->keyBy('alumno_id');
        $result = [];
        foreach ($alumnos as $alumno) {
            $cal = $existingCals->get($alumno->id);
            if (!$cal) {
                $cal = Calificacion::create([
                    'alumno_id' => $alumno->id,
                    'materia_id' => $materiaId,
                    'grupo_id' => $grupoId,
                    'periodo' => '',
                    'estatus' => 'cursando',
                ]);
            }
            $result[] = [
                'id' => $cal->id,
                'alumno_id' => $alumno->id,
                'alumno' => ['id' => $alumno->id, 'nombre_completo' => $alumno->user?->nombre_completo, 'matricula' => $alumno->matricula],
                'materia_id' => $materiaId,
                'grupo_id' => $grupoId,
                'parcial1' => $cal->parcial1,
                'parcial2' => $cal->parcial2,
                'parcial3' => $cal->parcial3,
                'ordinario' => $cal->ordinario,
                'extraordinario' => $cal->extraordinario,
                'promedio_final' => $cal->promedio_final,
                'estatus' => $cal->estatus,
                'asistencia_p1' => $cal->asistencia_p1 ?? 0,
                'asistencia_p2' => $cal->asistencia_p2 ?? 0,
                'asistencia_p3' => $cal->asistencia_p3 ?? 0,
            ];
        }
        return $this->success($result, 'Calificaciones del grupo.');
    }

    public function guardarLote(Request $request): JsonResponse
    {
        $data = $request->validate(['calificaciones' => 'required|array']);
        $updated = 0;
        foreach ($data['calificaciones'] as $item) {
            $cal = Calificacion::find($item['id'] ?? 0);
            if (!$cal) continue;
            $update = [];
            foreach (['parcial1','parcial2','parcial3','ordinario','extraordinario','asistencia_p1','asistencia_p2','asistencia_p3'] as $f) {
                if (array_key_exists($f, $item)) {
                    $val = $item[$f];
                    if ($val !== null && $val !== '') {
                        if (in_array($f, ['parcial1','parcial2','parcial3','ordinario','extraordinario'])) {
                            $val = max(5, min(10, (int)$val));
                        }
                    }
                    $update[$f] = $val === '' ? null : $val;
                }
            }
            if (!empty($update)) {
                $cal->update($update);
                $p1 = $cal->parcial1; $p2 = $cal->parcial2; $p3 = $cal->parcial3;
                if (is_numeric($p1) && is_numeric($p2) && is_numeric($p3)) {
                    $cal->update(['promedio_final' => round(($p1+$p2+$p3)/3, 1)]);
                }
                $updated++;
            }
        }
        return $this->success(['actualizadas' => $updated], "{$updated} calificaciones guardadas.");
    }

    public function sincronizarGrupoMateria(int $grupoId, int $materiaId, CalificacionService $service): JsonResponse
    {
        $alumnos = Alumno::where('grupo_id', $grupoId)->get();
        $creadas = 0;
        foreach ($alumnos as $alumno) {
            $cal = Calificacion::firstOrCreate(
                ['alumno_id' => $alumno->id, 'materia_id' => $materiaId],
                ['grupo_id' => $grupoId, 'estatus' => 'cursando']
            );
            if ($cal->wasRecentlyCreated) $creadas++;
        }
        return $this->success(['creadas' => $creadas], "{$creadas} calificaciones sincronizadas.");
    }
}
