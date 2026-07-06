<?php
namespace App\Modules\Alumnos\Services;

use App\Services\BaseService;
use App\Modules\Alumnos\Repositories\AlumnoRepository;
use App\Helpers\AuditoriaHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class AlumnoService extends BaseService
{
    public function __construct(protected AlumnoRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function filtrar(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->filtrar($filters, $perPage);
    }

    public function create(array $data): Model
    {
        $alumno = $this->repository->create($data);
        AuditoriaHelper::registrar('create', 'alumnos', $alumno->id, null, $alumno->toArray());
        return $alumno;
    }

    public function update(int $id, array $data): Model
    {
        $anterior = $this->repository->find($id)?->toArray();
        $alumno = $this->repository->update($id, $data);
        AuditoriaHelper::registrar('update', 'alumnos', $id, $anterior, $alumno->toArray());
        return $alumno;
    }

    public function delete(int $id): bool
    {
        $alumno = $this->repository->find($id);
        AuditoriaHelper::registrar('delete', 'alumnos', $id, $alumno?->toArray());
        return $this->repository->delete($id);
    }

    public function bloquear(int $id): Model
    {
        $alumno = $this->repository->update($id, ['estatus' => 'bloqueado']);
        AuditoriaHelper::registrar('update', 'alumnos', $id, null, ['estatus' => 'bloqueado']);
        return $alumno;
    }

    public function desbloquear(int $id): Model
    {
        $alumno = $this->repository->update($id, ['estatus' => 'activo']);
        AuditoriaHelper::registrar('update', 'alumnos', $id, null, ['estatus' => 'activo']);
        return $alumno;
    }

    public function historialAcademico(int $id): array
    {
        return $this->repository->historialAcademico($id);
    }
}
