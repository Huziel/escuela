<?php
namespace App\Modules\Calificaciones\Resources;
use Illuminate\Http\Request; use Illuminate\Http\Resources\Json\JsonResource;
class CalificacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,'periodo'=>$this->periodo,
            'parcial1'=>$this->parcial1,'parcial2'=>$this->parcial2,'parcial3'=>$this->parcial3,
            'ordinario'=>$this->ordinario,'extraordinario'=>$this->extraordinario,
            'promedio_final'=>$this->promedio_final,'redondeo'=>$this->redondeo,'estatus'=>$this->estatus,
            'observaciones'=>$this->observaciones,
            'alumno'=>$this->whenLoaded('alumno'),
            'materia'=>$this->whenLoaded('materia'),
            'grupo'=>$this->whenLoaded('grupo'),
            'created_at'=>$this->created_at,
        ];
    }
}
