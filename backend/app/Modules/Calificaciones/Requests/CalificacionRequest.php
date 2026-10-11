<?php
namespace App\Modules\Calificaciones\Requests;
use Illuminate\Foundation\Http\FormRequest;
class CalificacionRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'alumno_id' => 'required|exists:alumnos,id',
            'materia_id' => 'required|exists:materias,id',
            'grupo_id' => 'required|exists:grupos,id',
            'docente_id' => 'nullable|exists:docentes,id',
            'periodo' => 'required|string|max:20',
            'parcial1' => 'nullable|numeric|min:0|max:10',
            'parcial2' => 'nullable|numeric|min:0|max:10',
            'parcial3' => 'nullable|numeric|min:0|max:10',
            'ordinario' => 'nullable|numeric|min:0|max:10',
            'extraordinario' => 'nullable|numeric|min:0|max:10',
            'redondeo' => 'boolean',
            'observaciones' => 'nullable|string|max:500',
        ];
    }
}
