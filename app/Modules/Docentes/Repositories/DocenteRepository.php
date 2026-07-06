<?php
namespace App\Modules\Docentes\Repositories;

use App\Repositories\BaseRepository;
use App\Modules\Docentes\Models\Docente;
use Illuminate\Pagination\LengthAwarePaginator;

class DocenteRepository extends BaseRepository
{
    protected function getModelClass(): string { return Docente::class; }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = ['user', 'especialidadRel', 'materias', 'grupos']): LengthAwarePaginator
    {
        return $this->model->with($relations)->paginate($perPage, $columns);
    }

    public function filtrar(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['user', 'especialidadRel', 'materias', 'grupos']);
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('numero_empleado', 'like', "%{$search}%")
                ->orWhere('especialidad', 'like', "%{$search}%")
                ->orWhereHas('user', fn($u) => $u->where('nombre', 'like', "%{$search}%")
                    ->orWhere('apellido_paterno', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
        }
        if (!empty($filters['estatus'])) $query->where('estatus', $filters['estatus']);
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findAll() { return $this->model->with(['user', 'especialidadRel'])->where('estatus', 'activo')->get(); }
}
