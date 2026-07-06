<?php
namespace App\Modules\Alumnos\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlumnoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'matricula' => $this->matricula,
            'curp' => $this->curp,
            'semestre' => $this->semestre,
            'estatus' => $this->estatus,
            'fecha_ingreso' => $this->fecha_ingreso?->format('Y-m-d'),
            'observaciones' => $this->observaciones,
            'especialidad_id' => $this->especialidad_id,
            'grupo_id' => $this->grupo_id,
            'tutor_id' => $this->tutor_id,
            'especialidad' => $this->whenLoaded('especialidad', fn() => ['id' => $this->especialidad->id, 'nombre' => $this->especialidad->nombre]),
            'grupo' => $this->whenLoaded('grupo', fn() => ['id' => $this->grupo->id, 'nombre' => $this->grupo->nombre]),
            'tutor' => $this->whenLoaded('tutor'),
            'usuario' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'username' => $this->user->username,
                'nombre' => $this->user->nombre,
                'apellido_paterno' => $this->user->apellido_paterno,
                'apellido_materno' => $this->user->apellido_materno,
                'nombre_completo' => $this->user->nombre_completo,
                'email' => $this->user->email,
                'telefono' => $this->user->telefono,
                'fotografia' => $this->user->fotografia 
                    ? asset('storage/' . $this->user->fotografia) 
                    : null,
            ]),
            'created_at' => $this->created_at,
            'asistencias' => $this->whenLoaded('asistencias', fn() => $this->asistencias->map(fn($a) => [
                'id' => $a->id,
                'fecha' => $a->fecha?->format('Y-m-d'),
                'estatus' => $a->estatus,
                'justificacion' => $a->justificacion,
            ])),
            'horarios' => $this->whenLoaded('grupo.horarios', fn() => $this->grupo?->horarios?->map(fn($h) => [
                'id' => $h->id,
                'dia' => $h->dia,
                'hora_inicio' => $h->hora_inicio,
                'hora_fin' => $h->hora_fin,
                'aula' => $h->aula,
                'materia' => $h->materia?->nombre,
                'docente' => $h->docente?->user?->nombre_completo ?? $h->docente?->user?->nombre,
            ]) ?? []),
        ];
    }
}
