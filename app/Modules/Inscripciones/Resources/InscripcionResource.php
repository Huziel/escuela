<?php
namespace App\Modules\Inscripciones\Resources;
use Illuminate\Http\Request; use Illuminate\Http\Resources\Json\JsonResource;
class InscripcionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,'tipo'=>$this->tipo,'periodo'=>$this->periodo,'estatus'=>$this->estatus,
            'fecha_solicitud'=>$this->fecha_solicitud?->format('Y-m-d'),
            'fecha_resolucion'=>$this->fecha_resolucion?->format('Y-m-d'),
            'documentos_completos'=>$this->documentos_completos,'observaciones'=>$this->observaciones,
            'alumno'=>$this->whenLoaded('alumno'),
            'grupo'=>$this->whenLoaded('grupo'),
            'materias'=>$this->whenLoaded('materias'),
            'created_at'=>$this->created_at,
        ];
    }
}
