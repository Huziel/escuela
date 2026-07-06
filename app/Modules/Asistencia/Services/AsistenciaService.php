<?php
namespace App\Modules\Asistencia\Services;
use App\Services\BaseService;
use App\Modules\Asistencia\Repositories\AsistenciaRepository;
use App\Helpers\AuditoriaHelper;
use Illuminate\Database\Eloquent\Model;

class AsistenciaService extends BaseService
{
    public function __construct(protected AsistenciaRepository $repository) { $this->repository = $repository; }
    public function filtrar(array $filters, int $perPage = 15) { return $this->repository->filtrar($filters,$perPage); }
    
    public function create(array $data): Model { $a = $this->repository->create($data + ['registrado_por'=>auth()->id()]); AuditoriaHelper::registrar('create','asistencias',$a->id,null,$a->toArray()); return $a; }
    public function update(int $id, array $data): Model { $ant = $this->repository->find($id)?->toArray(); $a = $this->repository->update($id,$data); AuditoriaHelper::registrar('update','asistencias',$id,$ant,$a->toArray()); return $a; }
    public function delete(int $id): bool { $a = $this->repository->find($id); AuditoriaHelper::registrar('delete','asistencias',$id,$a?->toArray()); return $this->repository->delete($id); }
    public function registrarLote(array $data): void { $this->repository->registrarLote($data); }
    public function justificar(int $id, string $justificacion) { $a = $this->repository->update($id,['estatus'=>'justificada','justificacion'=>$justificacion]); AuditoriaHelper::registrar('update','asistencias',$id,null,['estatus'=>'justificada']); return $a; }
}
