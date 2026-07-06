<?php
namespace App\Modules\Docentes\Services;

use App\Services\BaseService;
use App\Modules\Docentes\Repositories\DocenteRepository;
use App\Helpers\AuditoriaHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class DocenteService extends BaseService
{
    public function __construct(protected DocenteRepository $repository)
    {
        $this->repository = $repository;
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator { return $this->repository->paginate($perPage); }
    public function filtrar(array $filters, int $perPage = 15): LengthAwarePaginator { return $this->repository->filtrar($filters, $perPage); }
    
    public function create(array $data): Model { $d = $this->repository->create($data); AuditoriaHelper::registrar('create','docentes',$d->id,null,$d->toArray()); return $d; }
    public function update(int $id, array $data): Model { $ant = $this->repository->find($id)?->toArray(); $d = $this->repository->update($id,$data); AuditoriaHelper::registrar('update','docentes',$id,$ant,$d->toArray()); return $d; }
    public function delete(int $id): bool { $d = $this->repository->find($id); AuditoriaHelper::registrar('delete','docentes',$id,$d?->toArray()); return $this->repository->delete($id); }
    public function findAll() { return $this->repository->findAll(); }
}
