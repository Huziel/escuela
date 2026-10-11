<?php
namespace App\Modules\Asistencia\Resources;
use Illuminate\Http\Request; use Illuminate\Http\Resources\Json\JsonResource;
class AsistenciaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,'fecha'=>$this->fecha?->format('Y-m-d'),'estatus'=>$this->estatus,'justificacion'=>$this->justificacion,
            'alumno'=>$this->whenLoaded('alumno'),
            'materia'=>$this->whenLoaded('materia'),
            'grupo'=>$this->whenLoaded('grupo'),
            'registrador'=>$this->whenLoaded('registrador',fn()=>$this->registrador?->nombre_completo),
            'created_at'=>$this->created_at,
        ];
    }
}
