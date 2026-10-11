<?php
namespace App\Modules\Calificaciones\Repositories;
use App\Repositories\BaseRepository;
use App\Modules\Calificaciones\Models\Calificacion;
use Illuminate\Pagination\LengthAwarePaginator;

class CalificacionRepository extends BaseRepository
{
    protected function getModelClass(): string { return Calificacion::class; }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = ['alumno.user','materia','grupo']): LengthAwarePaginator
    {
        return $this->model->with($relations)->paginate($perPage, $columns);
    }

    public function filtrar(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['alumno.user','materia','grupo']);
        if (!empty($filters['alumno_id'])) $query->where('alumno_id',$filters['alumno_id']);
        if (!empty($filters['materia_id'])) $query->where('materia_id',$filters['materia_id']);
        if (!empty($filters['grupo_id'])) $query->where('grupo_id',$filters['grupo_id']);
        if (!empty($filters['docente_id'])) $query->where('docente_id',$filters['docente_id']);
        if (!empty($filters['periodo'])) $query->where('periodo',$filters['periodo']);
        if (!empty($filters['estatus'])) $query->where('estatus',$filters['estatus']);
        return $query->orderBy('created_at','desc')->paginate($perPage);
    }

    public function kardexPorAlumno(int $alumnoId): array
    {
        $calificaciones = $this->model->with(['materia','grupo'])
            ->where('alumno_id',$alumnoId)
            ->orderBy('periodo')
            ->get()
            ->groupBy('periodo');
        return $calificaciones->toArray();
    }

    public function calcularPromedio(Calificacion $cal): float
    {
        $notas = array_filter([$cal->parcial1, $cal->parcial2, $cal->parcial3]);
        if (empty($notas)) return 0;
        $promedio = array_sum($notas) / count($notas);
        if ($cal->ordinario) $promedio = ($promedio + $cal->ordinario) / 2;
        if ($cal->extraordinario) $promedio = $cal->extraordinario;
        return $cal->redondeo ? round($promedio) : round($promedio, 2);
    }
}
