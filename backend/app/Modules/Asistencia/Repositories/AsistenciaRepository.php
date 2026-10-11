<?php
namespace App\Modules\Asistencia\Repositories;
use App\Repositories\BaseRepository;
use App\Modules\Asistencia\Models\Asistencia;
use Illuminate\Pagination\LengthAwarePaginator;

class AsistenciaRepository extends BaseRepository
{
    protected function getModelClass(): string { return Asistencia::class; }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = ['alumno.user','materia','grupo']): LengthAwarePaginator
    {
        return $this->model->with($relations)->paginate($perPage, $columns);
    }

    public function filtrar(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['alumno.user','materia','grupo']);
        if (!empty($filters['alumno_id'])) $query->where('alumno_id',$filters['alumno_id']);
        if (!empty($filters['grupo_id'])) $query->where('grupo_id',$filters['grupo_id']);
        if (!empty($filters['materia_id'])) $query->where('materia_id',$filters['materia_id']);
        if (!empty($filters['fecha'])) $query->whereDate('fecha',$filters['fecha']);
        if (!empty($filters['estatus'])) $query->where('estatus',$filters['estatus']);
        if (!empty($filters['fecha_desde'])) $query->where('fecha','>=',$filters['fecha_desde']);
        if (!empty($filters['fecha_hasta'])) $query->where('fecha','<=',$filters['fecha_hasta']);
        return $query->orderBy('fecha','desc')->orderBy('created_at','desc')->paginate($perPage);
    }

    public function registrarLote(array $data): void
    {
        foreach ($data['asistencias'] as $a) {
            $this->model->create([
                'alumno_id' => $a['alumno_id'],
                'grupo_id' => $data['grupo_id'],
                'materia_id' => $data['materia_id'],
                'docente_id' => $data['docente_id'] ?? null,
                'fecha' => $data['fecha'],
                'estatus' => $a['estatus'] ?? 'asistencia',
                'justificacion' => $a['justificacion'] ?? null,
                'registrado_por' => auth()->id(),
            ]);
        }
    }
}
