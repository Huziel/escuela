<?php
namespace App\Modules\Tutores\Resources;
use Illuminate\Http\Request; use Illuminate\Http\Resources\Json\JsonResource;
class TutorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,'parentesco'=>$this->parentesco,'ocupacion'=>$this->ocupacion,'direccion'=>$this->direccion,'telefono_trabajo'=>$this->telefono_trabajo,
            'usuario'=>$this->whenLoaded('user',fn()=>['id'=>$this->user->id,'username'=>$this->user->username,'nombre_completo'=>$this->user->nombre_completo,'email'=>$this->user->email,'telefono'=>$this->user->telefono]),
            'alumnos'=>$this->whenLoaded('alumnos'),
            'created_at'=>$this->created_at,
        ];
    }
}
