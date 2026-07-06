<?php
namespace App\Modules\Docentes\Resources;
use Illuminate\Http\Request; use Illuminate\Http\Resources\Json\JsonResource;
class DocenteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'numero_empleado' => $this->numero_empleado, 'curp' => $this->curp,
            'especialidad' => $this->especialidad, 'especialidad_id' => $this->especialidad_id,
            'especialidad_nombre' => $this->whenLoaded('especialidadRel', fn() => $this->especialidadRel?->nombre), 'grado_academico' => $this->grado_academico,
            'rfc' => $this->rfc, 'estatus' => $this->estatus, 'fecha_ingreso' => $this->fecha_ingreso?->format('Y-m-d'),
            'materias' => $this->whenLoaded('materias', fn() => $this->materias->map(fn($m) => ['id' => $m->id, 'nombre' => $m->nombre])),
            'grupos' => $this->whenLoaded('grupos', fn() => $this->grupos->map(fn($g) => [
                'id' => $g->id, 'nombre' => $g->nombre, 'semestre' => $g->semestre, 'turno' => $g->turno,
                'alumnos_count' => $g->alumnos_count ?? $g->alumnos?->count() ?? 0,
                'alumnos' => $this->when($g->relationLoaded('alumnos'), fn() => $g->alumnos->map(fn($a) => [
                    'id' => $a->id, 'matricula' => $a->matricula,
                    'nombre_completo' => $a->user?->nombre_completo ?? $a->usuario?->nombre_completo,
                ])),
            ])),
            'horarios' => $this->whenLoaded('horarios', fn() => $this->horarios->map(fn($h) => [
                'id' => $h->id, 'dia' => $h->dia, 'hora_inicio' => $h->hora_inicio,
                'hora_fin' => $h->hora_fin, 'aula' => $h->aula,
                'materia' => $h->materia?->nombre,
                'grupo' => $h->grupo?->nombre,
            ])),
            'usuario' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id, 'username' => $this->user->username,
                'nombre_completo' => $this->user->nombre_completo, 'email' => $this->user->email,
                'fotografia' => $this->user->fotografia ? asset('storage/' . $this->user->fotografia) : null,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
