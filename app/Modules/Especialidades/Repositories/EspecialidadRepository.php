<?php
namespace App\Modules\Especialidades\Repositories;
use App\Repositories\BaseRepository;
use App\Modules\Especialidades\Models\Especialidad;
use Illuminate\Pagination\LengthAwarePaginator;

class EspecialidadRepository extends BaseRepository
{
    protected function getModelClass(): string { return Especialidad::class; }
    
    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        return $this->model->withCount(['alumnos','materias','grupos'])->paginate($perPage, $columns);
    }

    public function filtrar(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->withCount(['alumnos','materias','grupos']);
        if (!empty($filters['search'])) $query->where(function($q) use ($filters) { $q->where('nombre','like','%'.$filters['search'].'%')->orWhere('clave','like','%'.$filters['search'].'%'); });
        if (isset($filters['activo'])) $query->where('activo', $filters['activo']);
        return $query->orderBy('nombre')->paginate($perPage);
    }

    public function findAll() { return $this->model->where('activo', true)->get(); }
}
