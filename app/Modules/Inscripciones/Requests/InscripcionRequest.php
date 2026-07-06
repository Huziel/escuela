<?php
namespace App\Modules\Inscripciones\Requests;
use Illuminate\Foundation\Http\FormRequest;
class InscripcionRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'alumno_id' => 'required|exists:alumnos,id',
            'grupo_id' => 'required|exists:grupos,id',
            'tipo' => 'required|in:nuevo_ingreso,reinscripcion',
            'periodo' => 'required|string|max:20',
            'documentos_completos' => 'boolean',
            'observaciones' => 'nullable|string|max:500',
            'materia_ids' => 'nullable|array',
            'materia_ids.*' => 'exists:materias,id',
        ];
    }
}
