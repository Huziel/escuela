<?php
namespace App\Modules\Materias\Services;
use App\Services\BaseService;
use App\Modules\Materias\Repositories\MateriaRepository;
use App\Helpers\AuditoriaHelper;
use Illuminate\Database\Eloquent\Model;

class MateriaService extends BaseService
{
    public function __construct(protected MateriaRepository $repository) { $this->repository = $repository; }
    public function filtrar(array $filters, int $perPage = 15) { return $this->repository->filtrar($filters,$perPage); }
    public function create(array $data): Model { $m = $this->repository->create($data); AuditoriaHelper::registrar('create','materias',$m->id,null,$m->toArray()); return $m; }
    public function update(int $id, array $data): Model { $ant = $this->repository->find($id)?->toArray(); $m = $this->repository->update($id,$data); AuditoriaHelper::registrar('update','materias',$id,$ant,$m->toArray()); return $m; }
    public function delete(int $id): bool { $m = $this->repository->find($id); AuditoriaHelper::registrar('delete','materias',$id,$m?->toArray()); return $this->repository->delete($id); }
    public function findAll() { return $this->repository->findAll(); }
}
