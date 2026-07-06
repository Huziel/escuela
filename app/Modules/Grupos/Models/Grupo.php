<?php

namespace App\Modules\Grupos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Grupo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre', 'semestre', 'especialidad_id', 'turno',
        'capacidad', 'hora_inicio', 'hora_fin', 'tutor_id', 'ciclo_escolar', 'activo', 'aula_id',
    ];

    protected $casts = [
        'semestre' => 'integer',
        'capacidad' => 'integer',
        'activo' => 'boolean',
    ];

    public function especialidad() { return $this->belongsTo(\App\Modules\Especialidades\Models\Especialidad::class); }
    public function tutor() { return $this->belongsTo(\App\Modules\Docentes\Models\Docente::class, 'tutor_id'); }
    public function aula() { return $this->belongsTo(\App\Models\Aula::class); }
    public function alumnos() { return $this->hasMany(\App\Modules\Alumnos\Models\Alumno::class); }
    public function horarios() { return $this->hasMany(\App\Modules\Horarios\Models\Horario::class); }
    public function docentes() { return $this->belongsToMany(\App\Modules\Docentes\Models\Docente::class, 'docente_grupo'); }
    public function inscripciones() { return $this->hasMany(\App\Modules\Inscripciones\Models\Inscripcion::class); }
    public function calificaciones() { return $this->hasMany(\App\Modules\Calificaciones\Models\Calificacion::class); }
}
