<?php

namespace App\Modules\Alumnos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Alumno extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'matricula', 'curp', 'especialidad_id', 'semestre',
        'grupo_id', 'tutor_id', 'estatus', 'fecha_ingreso', 'observaciones',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'semestre' => 'integer',
    ];

    public function user() { return $this->belongsTo(\App\Models\User::class); }
    public function especialidad() { return $this->belongsTo(\App\Modules\Especialidades\Models\Especialidad::class); }
    public function grupo() { return $this->belongsTo(\App\Modules\Grupos\Models\Grupo::class); }
    public function tutor() { return $this->belongsTo(\App\Modules\Tutores\Models\Tutor::class); }
    public function inscripciones() { return $this->hasMany(\App\Modules\Inscripciones\Models\Inscripcion::class); }
    public function calificaciones() { return $this->hasMany(\App\Modules\Calificaciones\Models\Calificacion::class); }
    public function asistencias() { return $this->hasMany(\App\Modules\Asistencia\Models\Asistencia::class); }
    public function regularizaciones() { return $this->hasMany(\App\Modules\Regularizacion\Models\Regularizacion::class); }
}
