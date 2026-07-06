<?php
namespace App\Modules\Alumnos\Policies;

use App\Models\User;

class AlumnoPolicy
{
    public function listar(User $user): bool { return $user->can('alumnos.listar'); }
    public function crear(User $user): bool { return $user->can('alumnos.crear'); }
    public function editar(User $user): bool { return $user->can('alumnos.editar'); }
    public function eliminar(User $user): bool { return $user->can('alumnos.eliminar'); }
    public function importar(User $user): bool { return $user->can('alumnos.importar'); }
    public function exportar(User $user): bool { return $user->can('alumnos.exportar'); }
    public function bloquear(User $user): bool { return $user->can('alumnos.bloquear'); }
}
