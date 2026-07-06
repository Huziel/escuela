<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('usuarios.listar') || $user->hasRole('super_admin');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasPermissionTo('usuarios.listar') || $user->hasRole('super_admin');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('usuarios.crear') || $user->hasRole('super_admin');
    }

    public function update(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }
        return $user->hasPermissionTo('usuarios.editar') || $user->hasRole('super_admin');
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }
        return $user->hasPermissionTo('usuarios.eliminar') || $user->hasRole('super_admin');
    }

    public function bloqueo(User $user): bool
    {
        return $user->hasPermissionTo('usuarios.bloquear') || $user->hasRole('super_admin');
    }
}
