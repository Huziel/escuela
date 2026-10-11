<?php
namespace App\Modules\Grupos\Resources;
use Illuminate\Http\Request; use Illuminate\Http\Resources\Json\JsonResource;
class GrupoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id, 'nombre'=>$this->nombre, 'semestre'=>$this->semestre, 'turno'=>$this->turno,
            'capacidad'=>$this->capacidad, 'ciclo_escolar'=>$this->ciclo_escolar, 'activo'=>$this->activo,
            'especialidad'=>$this->whenLoaded('especialidad'),
            'tutor'=>$this->whenLoaded('tutor'),
            'alumnos'=>$this->whenLoaded('alumnos'),
            'horarios'=>$this->whenLoaded('horarios'),
            'alumnos_count'=>$this->when($this->alumnos_count, $this->alumnos_count),
            'created_at'=>$this->created_at,
        ];
    }
}
