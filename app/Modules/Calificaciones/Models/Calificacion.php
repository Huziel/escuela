<?php

namespace App\Modules\Calificaciones\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Calificacion extends Model
{
    use SoftDeletes;

    protected $table = 'calificaciones';

    protected $fillable = [
        'alumno_id', 'materia_id', 'grupo_id', 'docente_id', 'periodo',
        'parcial1', 'parcial2', 'parcial3', 'ordinario', 'extraordinario',
        'promedio_final', 'redondeo', 'estatus', 'observaciones',
    ];

    protected $casts = [
        'redondeo' => 'boolean',
    ];

    public function alumno() { return $this->belongsTo(\App\Modules\Alumnos\Models\Alumno::class); }
    public function materia() { return $this->belongsTo(\App\Modules\Materias\Models\Materia::class); }
    public function grupo() { return $this->belongsTo(\App\Modules\Grupos\Models\Grupo::class); }
    public function docente() { return $this->belongsTo(\App\Modules\Docentes\Models\Docente::class); }
}
