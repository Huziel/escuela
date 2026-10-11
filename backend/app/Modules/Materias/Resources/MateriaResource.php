<?php
namespace App\Modules\Materias\Resources;
use Illuminate\Http\Request; use Illuminate\Http\Resources\Json\JsonResource;
class MateriaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,'clave'=>$this->clave,'nombre'=>$this->nombre,'creditos'=>$this->creditos,
            'horas_teoricas'=>$this->horas_teoricas,'horas_practicas'=>$this->horas_practicas,
            'semestre'=>$this->semestre,'activo'=>$this->activo,
            'especialidad'=>$this->whenLoaded('especialidad'),
            'created_at'=>$this->created_at,
        ];
    }
}
