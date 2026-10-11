<?php
namespace App\Modules\Asistencia\Requests;
use Illuminate\Foundation\Http\FormRequest;
class AsistenciaRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'alumno_id' => 'required|exists:alumnos,id',
            'grupo_id' => 'required|exists:grupos,id',
            'materia_id' => 'required|exists:materias,id',
            'docente_id' => 'nullable|exists:docentes,id',
            'fecha' => 'required|date',
            'estatus' => 'required|in:asistencia,retardo,falta,justificada',
            'justificacion' => 'nullable|string|max:500',
        ];
    }
}
