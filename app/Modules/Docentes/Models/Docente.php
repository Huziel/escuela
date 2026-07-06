<?php

namespace App\Modules\Docentes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Docente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'docentes';

    protected $fillable = [
        'user_id', 'numero_empleado', 'curp', 'especialidad', 'especialidad_id',
        'grado_academico', 'rfc', 'estatus', 'fecha_ingreso',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
    ];

    public function user() { return $this->belongsTo(\App\Models\User::class); }
    public function materias() { return $this->belongsToMany(\App\Modules\Materias\Models\Materia::class, 'docente_materia'); }
    public function grupos() { return $this->belongsToMany(\App\Modules\Grupos\Models\Grupo::class, 'docente_grupo'); }
    public function gruposTutor() { return $this->hasMany(\App\Modules\Grupos\Models\Grupo::class, 'tutor_id'); }
    public function horarios() { return $this->hasMany(\App\Modules\Horarios\Models\Horario::class); }
    public function especialidadRel() { return $this->belongsTo(\App\Modules\Especialidades\Models\Especialidad::class, 'especialidad_id'); }
}
