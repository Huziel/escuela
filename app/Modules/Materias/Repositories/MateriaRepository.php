<?php
namespace App\Modules\Materias\Repositories;
use App\Repositories\BaseRepository;
use App\Modules\Materias\Models\Materia;
use Illuminate\Pagination\LengthAwarePaginator;

class MateriaRepository extends BaseRepository
{
    protected function getModelClass(): string { return Materia::class; }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = ['especialidad']): LengthAwarePaginator
    {
        return $this->model->with($relations)->paginate($perPage, $columns);
    }

    public function filtrar(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['especialidad']);
        if (!empty($filters['search'])) $query->where(function($q) use($filters){ $q->where('nombre','like','%'.$filters['search'].'%')->orWhere('clave','like','%'.$filters['search'].'%'); });
        if (!empty($filters['especialidad_id'])) $query->where('especialidad_id',$filters['especialidad_id']);
        if (!empty($filters['semestre'])) $query->where('semestre',$filters['semestre']);
        if (isset($filters['activo'])) $query->where('activo',$filters['activo']);
        return $query->orderBy('semestre')->orderBy('nombre')->paginate($perPage);
    }

    public function findAll() { return $this->model->with('especialidad')->where('activo',true)->get(); }
}
