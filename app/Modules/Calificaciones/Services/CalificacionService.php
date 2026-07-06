<?php
namespace App\Modules\Calificaciones\Services;
use App\Services\BaseService;
use App\Modules\Calificaciones\Repositories\CalificacionRepository;
use App\Helpers\AuditoriaHelper;
use Illuminate\Database\Eloquent\Model;

class CalificacionService extends BaseService
{
    public function __construct(protected CalificacionRepository $repository) { $this->repository = $repository; }
    public function filtrar(array $filters, int $perPage = 15) { return $this->repository->filtrar($filters,$perPage); }
    
    public function create(array $data): Model {
        $data['promedio_final'] = 0;
        $cal = $this->repository->create($data);
        $this->recalcular($cal);
        AuditoriaHelper::registrar('create','calificaciones',$cal->id,null,$cal->fresh()->toArray());
        return $cal->fresh();
    }

    public function update(int $id, array $data): Model {
        $ant = $this->repository->find($id)?->toArray();
        $cal = $this->repository->update($id, $data);
        $this->recalcular($cal);
        AuditoriaHelper::registrar('update','calificaciones',$id,$ant,$cal->fresh()->toArray());
        return $cal->fresh();
    }

    private function recalcular($cal): void {
        $prom = $this->repository->calcularPromedio($cal);
        $estatus = 'cursando';
        if ($prom >= 6) $estatus = 'aprobada';
        elseif ($prom > 0 && $prom < 6) $estatus = 'reprobada';
        $cal->update(['promedio_final' => $prom, 'estatus' => $estatus]);
    }

    public function delete(int $id): bool { $c=$this->repository->find($id); AuditoriaHelper::registrar('delete','calificaciones',$id,$c?->toArray()); return $this->repository->delete($id); }
    public function kardex(int $alumnoId): array { return $this->repository->kardexPorAlumno($alumnoId); }
}
