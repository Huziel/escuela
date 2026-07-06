<?php
namespace App\Modules\Alumnos\Repositories;

use App\Repositories\BaseRepository;
use App\Modules\Alumnos\Models\Alumno;
use Illuminate\Pagination\LengthAwarePaginator;

class AlumnoRepository extends BaseRepository
{
    protected function getModelClass(): string
    {
        return Alumno::class;
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = ['user', 'especialidad', 'grupo', 'tutor']): LengthAwarePaginator
    {
        return $this->model->with($relations)->paginate($perPage, $columns);
    }

    public function filtrar(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['user', 'especialidad', 'grupo', 'tutor']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('matricula', 'like', "%{$search}%")
                  ->orWhere('curp', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('nombre', 'like', "%{$search}%")
                      ->orWhere('apellido_paterno', 'like', "%{$search}%")
                      ->orWhere('apellido_materno', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%"));
            });
        }
        if (!empty($filters['especialidad_id'])) $query->where('especialidad_id', $filters['especialidad_id']);
        if (!empty($filters['semestre'])) $query->where('semestre', $filters['semestre']);
        if (!empty($filters['grupo_id'])) $query->where('grupo_id', $filters['grupo_id']);
        if (!empty($filters['estatus'])) $query->where('estatus', $filters['estatus']);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function historialAcademico(int $alumnoId): array
    {
        $alumno = $this->findOrFail($alumnoId, ['calificaciones.materia', 'asistencias', 'inscripciones']);
        return [
            'alumno' => $alumno->load('user', 'especialidad', 'grupo'),
            'calificaciones' => $alumno->calificaciones,
            'asistencias' => $alumno->asistencias,
        ];
    }
}
