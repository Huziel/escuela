<?php
namespace App\Modules\Especialidades\Services;
use App\Services\BaseService;
use App\Modules\Especialidades\Repositories\EspecialidadRepository;
use App\Helpers\AuditoriaHelper;
use Illuminate\Database\Eloquent\Model;

class EspecialidadService extends BaseService
{
    public function __construct(protected EspecialidadRepository $repository) { $this->repository = $repository; }
    public function filtrar(array $filters, int $perPage = 15) { return $this->repository->filtrar($filters, $perPage); }
    public function create(array $data): Model { $e = $this->repository->create($data); AuditoriaHelper::registrar('create','especialidades',$e->id,null,$e->toArray()); return $e; }
    public function update(int $id, array $data): Model { $ant = $this->repository->find($id)?->toArray(); $e = $this->repository->update($id,$data); AuditoriaHelper::registrar('update','especialidades',$id,$ant,$e->toArray()); return $e; }
    public function delete(int $id): bool { $e = $this->repository->find($id); AuditoriaHelper::registrar('delete','especialidades',$id,$e?->toArray()); return $this->repository->delete($id); }
    public function findAll() { return $this->repository->findAll(); }
}
