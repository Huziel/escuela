<?php
namespace App\Modules\Grupos\Services;
use App\Services\BaseService;
use App\Modules\Grupos\Repositories\GrupoRepository;
use App\Helpers\AuditoriaHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class GrupoService extends BaseService
{
    public function __construct(protected GrupoRepository $repository) { $this->repository = $repository; }
    public function paginate(int $perPage = 15): LengthAwarePaginator { return $this->repository->paginate($perPage); }
    public function filtrar(array $filters, int $perPage = 15): LengthAwarePaginator { return $this->repository->filtrar($filters,$perPage); }
    public function create(array $data): Model { $g=$this->repository->create($data); AuditoriaHelper::registrar('create','grupos',$g->id,null,$g->toArray()); return $g; }
    public function update(int $id, array $data): Model { $ant=$this->repository->find($id)?->toArray(); $g=$this->repository->update($id,$data); AuditoriaHelper::registrar('update','grupos',$id,$ant,$g->toArray()); return $g; }
    public function delete(int $id): bool { $g=$this->repository->find($id); AuditoriaHelper::registrar('delete','grupos',$id,$g?->toArray()); return $this->repository->delete($id); }
    public function findAll() { return $this->repository->findAll(); }
    public function findWithAlumnos(int $id) { return $this->repository->findWithAlumnos($id); }
}
