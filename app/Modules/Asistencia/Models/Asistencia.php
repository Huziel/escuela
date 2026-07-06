<?php

namespace App\Modules\Asistencia\Models;

use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    protected $fillable = [
        'alumno_id', 'grupo_id', 'materia_id', 'docente_id',
        'fecha', 'estatus', 'justificacion', 'registrado_por',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function alumno() { return $this->belongsTo(\App\Modules\Alumnos\Models\Alumno::class); }
    public function grupo() { return $this->belongsTo(\App\Modules\Grupos\Models\Grupo::class); }
    public function materia() { return $this->belongsTo(\App\Modules\Materias\Models\Materia::class); }
    public function docente() { return $this->belongsTo(\App\Modules\Docentes\Models\Docente::class); }
    public function registrador() { return $this->belongsTo(\App\Models\User::class, 'registrado_por'); }
}
