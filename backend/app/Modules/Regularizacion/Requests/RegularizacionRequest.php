<?php
namespace App\Modules\Regularizacion\Requests;
use Illuminate\Foundation\Http\FormRequest;
class RegularizacionRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'alumno_id' => 'required|exists:alumnos,id',
            'materia_id' => 'required|exists:materias,id',
            'calificacion_id' => 'nullable|exists:calificaciones,id',
            'periodo' => 'required|string|max:20',
            'fecha_examen' => 'required|date',
            'calificacion' => 'nullable|numeric|min:0|max:10',
            'pago' => 'nullable|numeric|min:0',
            'pagado' => 'boolean',
            'observaciones' => 'nullable|string|max:500',
        ];
    }
}
