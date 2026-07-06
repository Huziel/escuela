<?php
namespace App\Modules\Alumnos\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AlumnoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $nullable = ['curp', 'grupo_id', 'tutor_id', 'observaciones'];
        foreach ($nullable as $field) {
            if ($this->has($field) && ($this->$field === '' || $this->$field === 'null')) {
                $this->merge([$field => null]);
            }
        }
    }
    
    public function rules(): array
    {
        $rules = [
            'matricula' => 'required|string|max:20|unique:alumnos,matricula,' . $this->route('id'),
            'curp' => 'nullable|string|max:18',
            'especialidad_id' => 'required|exists:especialidades,id',
            'semestre' => 'required|integer|min:1|max:12',
            'grupo_id' => 'nullable|exists:grupos,id',
            'tutor_id' => 'nullable|exists:docentes,id',
            'estatus' => 'required|in:activo,inactivo,egresado,baja,bloqueado',
            'fecha_ingreso' => 'required|date',
            'observaciones' => 'nullable|string|max:500',
        ];
        if ($this->isMethod('post')) {
            $rules['user_id'] = 'required|exists:users,id';
        }
        return $rules;
    }
}
