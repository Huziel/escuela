<?php
namespace App\Modules\Inscripciones\Services;
use App\Services\BaseService;
use App\Modules\Inscripciones\Repositories\InscripcionRepository;
use App\Helpers\AuditoriaHelper;
use Illuminate\Database\Eloquent\Model;

class InscripcionService extends BaseService
{
    public function __construct(protected InscripcionRepository $repository) { $this->repository = $repository; }
    public function filtrar(array $filters, int $perPage = 15) { return $this->repository->filtrar($filters,$perPage); }
    public function create(array $data): Model { $i = $this->repository->create($data); AuditoriaHelper::registrar('create','inscripciones',$i->id,null,$i->toArray()); return $i; }
    public function update(int $id, array $data): Model { $ant = $this->repository->find($id)?->toArray(); $i = $this->repository->update($id,$data); AuditoriaHelper::registrar('update','inscripciones',$id,$ant,$i->toArray()); return $i; }
    public function delete(int $id): bool { $i = $this->repository->find($id); AuditoriaHelper::registrar('delete','inscripciones',$id,$i?->toArray()); return $this->repository->delete($id); }
    public function aprobar(int $id) { $i = $this->repository->aprobar($id); AuditoriaHelper::registrar('update','inscripciones',$id,null,['estatus'=>'aprobada']); return $i; }
    public function rechazar(int $id, string $obs = null) { $i = $this->repository->rechazar($id,$obs); AuditoriaHelper::registrar('update','inscripciones',$id,null,['estatus'=>'rechazada']); return $i; }
}
