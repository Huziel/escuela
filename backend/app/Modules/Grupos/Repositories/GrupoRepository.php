<?php
namespace App\Modules\Grupos\Repositories;
use App\Repositories\BaseRepository;
use App\Modules\Grupos\Models\Grupo;
use Illuminate\Pagination\LengthAwarePaginator;

class GrupoRepository extends BaseRepository
{
    protected function getModelClass(): string { return Grupo::class; }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = ['especialidad','tutor.user']): LengthAwarePaginator
    {
        return $this->model->with($relations)->paginate($perPage, $columns);
    }

    public function filtrar(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['especialidad','tutor.user']);
        if (!empty($filters['search'])) {
            $query->where('nombre', 'like', '%' . $filters['search'] . '%');
        }
        if(!empty($filters['especialidad_id'])) $query->where('especialidad_id',$filters['especialidad_id']);
        if(!empty($filters['semestre'])) $query->where('semestre',$filters['semestre']);
        if(!empty($filters['turno'])) $query->where('turno',$filters['turno']);
        if(!empty($filters['activo'])) $query->where('activo',$filters['activo']);
        return $query->orderBy('semestre')->orderBy('nombre')->paginate($perPage);
    }

    public function findAll() { return $this->model->with('especialidad')->where('activo',true)->get(); }
    
    public function findWithAlumnos(int $id): ?Grupo
    {
        return $this->model->with(['alumnos.user','horarios.materia','horarios.docente.user'])->find($id);
    }
}
