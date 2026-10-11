<?php
namespace App\Modules\Tutores\Repositories;
use App\Repositories\BaseRepository;
use App\Modules\Tutores\Models\Tutor;
use Illuminate\Pagination\LengthAwarePaginator;

class TutorRepository extends BaseRepository
{
    protected function getModelClass(): string { return Tutor::class; }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = ['user','alumnos.user']): LengthAwarePaginator
    {
        return $this->model->with($relations)->paginate($perPage, $columns);
    }

    public function filtrar(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['user','alumnos.user']);
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->whereHas('user',fn($u)=>$u->where('nombre','like',"%{$s}%")->orWhere('apellido_paterno','like',"%{$s}%")->orWhere('email','like',"%{$s}%"));
        }
        return $query->orderBy('created_at','desc')->paginate($perPage);
    }

    public function findAll() { return $this->model->with('user')->get(); }
}
