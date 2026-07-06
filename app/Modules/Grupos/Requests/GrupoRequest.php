<?php
namespace App\Modules\Grupos\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GrupoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        if ($this->input('hora_inicio') === '') $this->merge(['hora_inicio' => null]);
        if ($this->input('hora_fin') === '') $this->merge(['hora_fin' => null]);
        if ($this->input('tutor_id') === '') $this->merge(['tutor_id' => null]);
        if ($this->input('aula_id') === '') $this->merge(['aula_id' => null]);
        if ($this->input('ciclo_escolar') === '') $this->merge(['ciclo_escolar' => null]);
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:50|unique:grupos,nombre,' . $this->route('id'),
            'semestre' => 'required|integer|min:1|max:12',
            'especialidad_id' => 'required|exists:especialidades,id',
            'turno' => 'required|in:matutino,vespertino,nocturno',
            'capacidad' => 'required|integer|min:1',
            'hora_inicio' => 'nullable|string',
            'hora_fin' => 'nullable|string',
            'tutor_id' => 'nullable|exists:docentes,id',
            'aula_id' => [
                'nullable',
                'exists:aulas,id',
                Rule::unique('grupos', 'aula_id')
                    ->where('ciclo_escolar', $this->ciclo_escolar)
                    ->ignore($this->route('id')),
            ],
            'ciclo_escolar' => 'nullable|string|max:20',
            'activo' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'aula_id.unique' => 'Esta aula ya está asignada a otro grupo en el mismo ciclo escolar.',
        ];
    }
}
