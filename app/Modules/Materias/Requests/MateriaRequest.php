<?php
namespace App\Modules\Materias\Requests;
use Illuminate\Foundation\Http\FormRequest;
class MateriaRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'clave' => 'nullable|string|max:20|unique:materias,clave,'.$this->route('id'),
            'nombre' => 'required|string|max:200',
            'creditos' => 'nullable|integer|min:1',
            'horas_teoricas' => 'nullable|integer|min:0',
            'horas_practicas' => 'nullable|integer|min:0',
            'semestre' => 'required|integer|min:1|max:12',
            'especialidad_id' => 'required|exists:especialidades,id',
            'activo' => 'boolean',
        ];
    }
}
