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
            'ciclo_escolar' => $this->ciclo_escolar,
            'turno' => $this->turno,
            'especialidad_id' => $this->especialidad_id,
            'grupo_id' => $this->grupo_id,
            'especialidad' => $this->whenLoaded('especialidad', fn() => ['id' => $this->especialidad->id, 'nombre' => $this->especialidad->nombre]),
            'grupo' => $this->whenLoaded('grupo', fn() => ['id' => $this->grupo->id, 'nombre' => $this->grupo->nombre, 'turno' => $this->grupo->turno]),
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
                'direccion' => $this->user->direccion,

                'fotografia' => $this->user->fotografia 
                    ? asset('storage/' . $this->user->fotografia) 
                    : null,
            ]),
            'created_at' => $this->created_at,
            'calificaciones' => $this->whenLoaded('calificaciones', fn() => $this->calificaciones->map(fn($c) => [
                'id' => $c->id,
                'materia_id' => $c->materia_id,
                'materia' => $c->materia ? ['id' => $c->materia->id, 'nombre' => $c->materia->nombre] : null,
                'parcial1' => $c->parcial1,
                'parcial2' => $c->parcial2,
                'parcial3' => $c->parcial3,
                'ordinario' => $c->ordinario,
                'extraordinario' => $c->extraordinario,
                'promedio_final' => $c->promedio_final,
                'estatus' => $c->estatus,
                'asistencia_p1' => $c->asistencia_p1,
                'asistencia_p2' => $c->asistencia_p2,
                'asistencia_p3' => $c->asistencia_p3,
                'periodo' => $c->periodo,
            ])),
            'asistencias' => $this->whenLoaded('asistencias', fn() => $this->asistencias->map(fn($a) => [
                'id' => $a->id,
                'fecha' => $a->fecha?->format('Y-m-d'),
                'estatus' => $a->estatus,
                'justificacion' => $a->justificacion,
            ])),
            'horarios' => $this->when($this->relationLoaded('grupo') && $this->grupo?->relationLoaded('horarios'), fn() => $this->grupo->horarios->map(fn($h) => [
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
