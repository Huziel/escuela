<?php

namespace App\Modules\Materias\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Materia extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'clave', 'nombre', 'creditos', 'horas_teoricas',
        'horas_practicas', 'semestre', 'especialidad_id', 'activo',
    ];

    protected $casts = [
        'creditos' => 'integer',
        'horas_teoricas' => 'integer',
        'horas_practicas' => 'integer',
        'semestre' => 'integer',
        'activo' => 'boolean',
    ];

    public function especialidad() { return $this->belongsTo(\App\Modules\Especialidades\Models\Especialidad::class); }
    public function planes() { return $this->belongsToMany(\App\Models\PlanEstudio::class, 'plan_materia', 'materia_id', 'plan_id')->withPivot('semestre', 'horas'); }
    public function docentes() { return $this->belongsToMany(\App\Modules\Docentes\Models\Docente::class, 'docente_materia'); }
    public function horarios() { return $this->hasMany(\App\Modules\Horarios\Models\Horario::class); }
    public function calificaciones() { return $this->hasMany(\App\Modules\Calificaciones\Models\Calificacion::class); }
}
