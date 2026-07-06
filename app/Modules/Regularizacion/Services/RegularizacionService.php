<?php
namespace App\Modules\Regularizacion\Services;
use App\Services\BaseService;
use App\Modules\Regularizacion\Repositories\RegularizacionRepository;
use App\Helpers\AuditoriaHelper;
use Illuminate\Database\Eloquent\Model;

class RegularizacionService extends BaseService
{
    public function __construct(protected RegularizacionRepository $repository) { $this->repository = $repository; }
    public function filtrar(array $filters, int $perPage = 15) { return $this->repository->filtrar($filters,$perPage); }
    public function create(array $data): Model { $r=$this->repository->create($data); AuditoriaHelper::registrar('create','regularizaciones',$r->id,null,$r->toArray()); return $r; }
    public function update(int $id, array $data): Model { 
        $ant=$this->repository->find($id)?->toArray(); $r=$this->repository->update($id,$data);
        if (isset($data['calificacion']) && $data['calificacion'] >= 6) { $r->update(['estatus'=>'aprobada']); } elseif (isset($data['calificacion'])) { $r->update(['estatus'=>'reprobada']); }
        AuditoriaHelper::registrar('update','regularizaciones',$id,$ant,$r->fresh()->toArray()); return $r->fresh(); 
    }
    public function delete(int $id): bool { $r=$this->repository->find($id); AuditoriaHelper::registrar('delete','regularizaciones',$id,$r?->toArray()); return $this->repository->delete($id); }
}
