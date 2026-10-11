<?php
namespace App\Modules\Regularizacion\Resources;
use Illuminate\Http\Request; use Illuminate\Http\Resources\Json\JsonResource;
class RegularizacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,'periodo'=>$this->periodo,'fecha_examen'=>$this->fecha_examen?->format('Y-m-d'),
            'calificacion'=>$this->calificacion,'estatus'=>$this->estatus,'pago'=>$this->pago,'pagado'=>$this->pagado,'observaciones'=>$this->observaciones,
            'alumno'=>$this->whenLoaded('alumno'),
            'materia'=>$this->whenLoaded('materia'),
            'created_at'=>$this->created_at,
        ];
    }
}
