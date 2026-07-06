<?php
namespace App\Modules\Especialidades\Requests;
use Illuminate\Foundation\Http\FormRequest;
class EspecialidadRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'clave' => 'required|string|max:20|unique:especialidades,clave,'.$this->route('especialidad'),
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string|max:1000',
            'activo' => 'boolean',
        ];
    }
}
