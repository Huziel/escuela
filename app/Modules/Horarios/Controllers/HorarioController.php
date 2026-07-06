<?php
namespace App\Modules\Horarios\Controllers;
use App\Http\Controllers\Controller; use App\Traits\ApiResponse;
use App\Modules\Horarios\Models\Horario;
use App\Modules\Grupos\Models\Grupo;
use App\Modules\Docentes\Models\Docente;
use App\Modules\Docentes\Resources\DocenteResource;
use Illuminate\Http\JsonResponse; use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HorarioController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Horario::with(['grupo','materia','docente.user']);
        if ($request->filled('grupo_id')) $query->where('grupo_id',$request->grupo_id);
        if ($request->filled('docente_id')) $query->where('docente_id',$request->docente_id);
        if ($request->filled('materia_id')) $query->where('materia_id',$request->materia_id);
        if ($request->filled('dia')) $query->where('dia',$request->dia);
        return $this->paginated($query->orderBy('dia')->orderBy('hora_inicio')->paginate($request->get('per_page',50)), 'Horarios obtenidos.');
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'grupo_id' => 'required|exists:grupos,id',
            'materia_id' => 'required|exists:materias,id',
            'docente_id' => 'required|exists:docentes,id',
            'dia' => 'required|string',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ]);

        $grupo = Grupo::findOrFail($data['grupo_id']);
        if ($grupo->hora_inicio && $grupo->hora_fin) {
            if ($data['hora_inicio'] < $grupo->hora_inicio) {
                return $this->error("La hora de inicio no puede ser anterior a las {$grupo->hora_inicio}.", 422);
            }
            if ($data['hora_fin'] > $grupo->hora_fin) {
                return $this->error("La hora de fin no puede ser posterior a las {$grupo->hora_fin}.", 422);
            }
        }

        $h = Horario::create($data);
        return $this->success($h->load(['grupo','materia','docente.user']), 'Horario creado.', 201);
    }

    public function show(int $id): JsonResponse
    {
        $h = Horario::with(['grupo','materia','docente.user'])->find($id);
        return $h ? $this->success($h) : $this->error('No encontrado.', 404);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $h = Horario::findOrFail($id);
        $data = $request->validate([
            'grupo_id' => 'exists:grupos,id', 'materia_id' => 'exists:materias,id', 'docente_id' => 'exists:docentes,id',
            'dia' => 'in:Lunes,Martes,Miércoles,Miercoles,Jueves,Viernes,lunes,martes,miercoles,jueves,viernes',
            'hora_inicio' => 'date_format:H:i','hora_fin' => 'date_format:H:i|after:hora_inicio',
        ]);
        $h->update($data);
        return $this->success($h->load(['grupo','materia','docente.user']), 'Actualizado.');
    }

    public function destroy(int $id): JsonResponse { Horario::findOrFail($id)->delete(); return $this->success(null,'Eliminado.'); }

    public function resumen(Request $request): JsonResponse
    {
        $request->validate(['grupo_id' => 'required|exists:grupos,id']);
        $horas = Horario::where('grupo_id', $request->grupo_id)
            ->select('dia', DB::raw("ROUND(SUM(TIME_TO_SEC(TIMEDIFF(hora_fin, hora_inicio))) / 3600, 1) as total_horas"))
            ->groupBy('dia')
            ->orderByRaw("FIELD(dia, 'Lunes','Martes','Miercoles','Jueves','Viernes')")
            ->get();
        return $this->success($horas, 'Resumen de horas.');
    }

    public function docentesPorMateria(Request $request): JsonResponse
    {
        $request->validate(['materia_id' => 'required|exists:materias,id']);
        $materia = \App\Modules\Materias\Models\Materia::find($request->materia_id);
        
        $docentes = Docente::where(function($q) use ($request, $materia) {
            $q->whereHas('materias', fn($sq) => $sq->where('materia_id', $request->materia_id));
            if ($materia->especialidad_id) {
                $q->orWhere('especialidad_id', $materia->especialidad_id);
            }
        })
        ->with('user')
        ->get();
        
        return $this->success(DocenteResource::collection($docentes), 'Docentes calificados para esta materia.');
    }
}
