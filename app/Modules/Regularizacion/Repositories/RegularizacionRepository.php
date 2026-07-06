<?php
namespace App\Modules\Regularizacion\Repositories;
use App\Repositories\BaseRepository;
use App\Modules\Regularizacion\Models\Regularizacion;
use Illuminate\Pagination\LengthAwarePaginator;

class RegularizacionRepository extends BaseRepository
{
    protected function getModelClass(): string { return Regularizacion::class; }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = ['alumno.user','materia']): LengthAwarePaginator
    {
        return $this->model->with($relations)->paginate($perPage, $columns);
    }

    public function filtrar(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['alumno.user','materia']);
        if (!empty($filters['alumno_id'])) $query->where('alumno_id',$filters['alumno_id']);
        if (!empty($filters['materia_id'])) $query->where('materia_id',$filters['materia_id']);
        if (!empty($filters['estatus'])) $query->where('estatus',$filters['estatus']);
        return $query->orderBy('created_at','desc')->paginate($perPage);
    }
}
