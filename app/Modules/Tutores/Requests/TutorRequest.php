<?php
namespace App\Modules\Tutores\Requests;
use Illuminate\Foundation\Http\FormRequest;
class TutorRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        $rules = [
            'parentesco' => 'required|in:padre,madre,tutor,otro',
            'ocupacion' => 'nullable|string|max:100',
            'direccion' => 'nullable|string|max:500',
            'telefono_trabajo' => 'nullable|string|max:20',
        ];
        if ($this->isMethod('post')) $rules['user_id'] = 'required|exists:users,id|unique:tutores,user_id';
        return $rules;
    }
}
