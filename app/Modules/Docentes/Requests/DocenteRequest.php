<?php
namespace App\Modules\Docentes\Requests;
use Illuminate\Foundation\Http\FormRequest;
class DocenteRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        $rules = [
            'numero_empleado' => 'required|string|max:20|unique:docentes,numero_empleado,'.$this->route('id'),
            'curp' => 'nullable|string|max:18',
            'especialidad' => 'nullable|string|max:200',
            'especialidad_id' => 'nullable|exists:especialidades,id',
            'grado_academico' => 'nullable|string|max:100',
            'rfc' => 'nullable|string|max:13',
            'estatus' => 'required|in:activo,inactivo,baja',
            'fecha_ingreso' => 'required|date',
        ];
        if ($this->isMethod('post')) $rules['user_id'] = 'required|exists:users,id';
        return $rules;
    }
}
