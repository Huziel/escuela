<?php
namespace App\Modules\Inscripciones\Repositories;
use App\Repositories\BaseRepository;
use App\Modules\Inscripciones\Models\Inscripcion;
use Illuminate\Pagination\LengthAwarePaginator;

class InscripcionRepository extends BaseRepository
{
    protected function getModelClass(): string { return Inscripcion::class; }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = ['alumno.user','grupo','materias']): LengthAwarePaginator
    {
        return $this->model->with($relations)->paginate($perPage, $columns);
    }

    public function filtrar(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['alumno.user','grupo','materias']);
        if (!empty($filters['alumno_id'])) $query->where('alumno_id',$filters['alumno_id']);
        if (!empty($filters['grupo_id'])) $query->where('grupo_id',$filters['grupo_id']);
        if (!empty($filters['tipo'])) $query->where('tipo',$filters['tipo']);
        if (!empty($filters['estatus'])) $query->where('estatus',$filters['estatus']);
        if (!empty($filters['periodo'])) $query->where('periodo',$filters['periodo']);
        return $query->orderBy('created_at','desc')->paginate($perPage);
    }

    public function aprobar(int $id): ?Inscripcion
    {
        $inscripcion = $this->findOrFail($id);
        $inscripcion->update(['estatus' => 'aprobada', 'fecha_resolucion' => now()]);
        return $inscripcion;
    }

    public function rechazar(int $id, string $observaciones = null): ?Inscripcion
    {
        $inscripcion = $this->findOrFail($id);
        $inscripcion->update(['estatus' => 'rechazada', 'fecha_resolucion' => now(), 'observaciones' => $observaciones]);
        return $inscripcion;
    }
}
