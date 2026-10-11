<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'nombre' => $this->nombre,
            'apellido_paterno' => $this->apellido_paterno,
            'apellido_materno' => $this->apellido_materno,
            'nombre_completo' => $this->nombre_completo,
            'email' => $this->email,
            'sexo' => $this->sexo,
            'fecha_nacimiento' => $this->fecha_nacimiento?->format('Y-m-d'),
            'telefono' => $this->telefono,
            'direccion' => $this->direccion,
            'fotografia' => $this->fotografia,
            'rol' => $this->rol,
            'activo' => $this->activo,
            'ultimo_acceso' => $this->ultimo_acceso?->format('Y-m-d H:i:s'),
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'permissions' => $r->permissions->pluck('name'),
            ])),
            'permissions' => $this->when(
                $this->relationLoaded('roles'),
                fn () => $this->getAllPermissions()->pluck('name')
            ),
            'alumno' => $this->whenLoaded('alumno', fn () => [
                'id' => $this->alumno->id,
                'matricula' => $this->alumno->matricula,
                'semestre' => $this->alumno->semestre,
                'estatus' => $this->alumno->estatus,
                'especialidad' => $this->alumno->especialidad?->nombre,
                'grupo' => $this->alumno->grupo?->nombre,
                'turno' => $this->alumno->turno ?? $this->alumno->grupo?->turno,
            ]),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
