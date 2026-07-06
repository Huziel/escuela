<?php

namespace App\Modules\Especialidades\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Especialidad extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'especialidades';

    protected $fillable = ['clave', 'nombre', 'descripcion', 'activo'];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function alumnos() { return $this->hasMany(\App\Modules\Alumnos\Models\Alumno::class); }
    public function materias() { return $this->hasMany(\App\Modules\Materias\Models\Materia::class); }
    public function grupos() { return $this->hasMany(\App\Modules\Grupos\Models\Grupo::class); }
}
