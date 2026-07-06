<?php
namespace App\Modules\Tutores\Services;
use App\Services\BaseService;
use App\Modules\Tutores\Repositories\TutorRepository;
use App\Helpers\AuditoriaHelper;
use Illuminate\Database\Eloquent\Model;

class TutorService extends BaseService
{
    public function __construct(protected TutorRepository $repository) { $this->repository = $repository; }
    public function filtrar(array $filters, int $perPage = 15) { return $this->repository->filtrar($filters,$perPage); }
    public function create(array $data): Model { $t=$this->repository->create($data); AuditoriaHelper::registrar('create','tutores',$t->id,null,$t->toArray()); return $t; }
    public function update(int $id, array $data): Model { $ant=$this->repository->find($id)?->toArray(); $t=$this->repository->update($id,$data); AuditoriaHelper::registrar('update','tutores',$id,$ant,$t->toArray()); return $t; }
    public function delete(int $id): bool { $t=$this->repository->find($id); AuditoriaHelper::registrar('delete','tutores',$id,$t?->toArray()); return $this->repository->delete($id); }
    public function findAll() { return $this->repository->findAll(); }
}
