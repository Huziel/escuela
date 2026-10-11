<?php
namespace App\Modules\Grupos\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

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
        if ($this->input('ciclo_escolar_id') === '') $this->merge(['ciclo_escolar_id' => null]);
    }

    public function rules(): array
    {
        // Determinar el ID a excluir: ruta (PUT /grupos/{id}) o body (grupo_id)
        $routeId = $this->route('id');
        $bodyGrupoId = $this->input('grupo_id');
        $bodyId = $this->input('id');

        // Prioridad: route('id') > input('grupo_id') > input('id')
        $rawId = $routeId ?? $bodyGrupoId ?? $bodyId;
        $ignoreId = is_numeric($rawId) ? (int) $rawId : null;

        // --- DEBUG: ver qué valor real se está usando ---
        Log::info('GrupoRequest::rules — ignoreId', [
            'route_id' => $routeId,
            'route_id_type' => gettype($routeId),
            'input_grupo_id' => $bodyGrupoId,
            'input_grupo_id_type' => gettype($bodyGrupoId),
            'input_id' => $bodyId,
            'input_id_type' => gettype($bodyId),
            'rawId' => $rawId,
            'rawId_type' => gettype($rawId),
            'ignoreId_final' => $ignoreId,
            'is_update' => $this->isMethod('PUT') || $this->isMethod('PATCH'),
            'request_method' => $this->method(),
        ]);

        // --- Regla unique para nombre ---
        $nombreUnique = Rule::unique('grupos', 'nombre')->withoutTrashed();
        if ($ignoreId) {
            $nombreUnique->whereNot('id', $ignoreId);
        }

        // --- Regla unique para aula_id (mismo ciclo_escolar + turno) ---
        $aulaUnique = Rule::unique('grupos', 'aula_id')
            ->where('ciclo_escolar', $this->ciclo_escolar)
            ->where('turno', $this->turno)
            ->withoutTrashed();
        if ($ignoreId) {
            $aulaUnique->whereNot('id', $ignoreId);
        }

        return [
            'grupo_id' => 'nullable|integer',
            'nombre' => ['required', 'string', 'max:50', $nombreUnique],
            'semestre' => 'required|integer|min:1|max:12',
            'especialidad_id' => 'required|exists:especialidades,id',
            'turno' => 'required|in:matutino,vespertino',
            'capacidad' => 'required|integer|min:1',
            'hora_inicio' => 'nullable|string',
            'hora_fin' => 'nullable|string',
            'tutor_id' => 'nullable|exists:docentes,id',
            'aula_id' => [
                'nullable',
                'exists:aulas,id',
                $aulaUnique,
            ],
            'ciclo_escolar' => 'nullable|string|max:20',
            'ciclo_escolar_id' => 'nullable|exists:ciclos_escolares,id',
            'activo' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.unique' => 'Ya existe un grupo con este nombre.',
            'aula_id.unique' => 'Esta aula ya está asignada a otro grupo en el mismo ciclo escolar y turno.',
        ];
    }
}
