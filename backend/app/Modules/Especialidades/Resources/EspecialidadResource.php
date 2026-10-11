<?php
namespace App\Modules\Especialidades\Resources;
use Illuminate\Http\Request; use Illuminate\Http\Resources\Json\JsonResource;
class EspecialidadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,'clave'=>$this->clave,'nombre'=>$this->nombre,'descripcion'=>$this->descripcion,'activo'=>$this->activo,
            'alumnos_count'=>$this->when($this->alumnos_count,$this->alumnos_count),
            'materias_count'=>$this->when($this->materias_count,$this->materias_count),
            'grupos_count'=>$this->when($this->grupos_count,$this->grupos_count),
            'created_at'=>$this->created_at,
        ];
    }
}
