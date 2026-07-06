<?php

namespace App\Modules\Horarios\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Horario extends Model
{
    use SoftDeletes;

    protected $table = 'horarios';

    protected $fillable = [
        'grupo_id', 'materia_id', 'docente_id', 'dia',
        'hora_inicio', 'hora_fin', 'aula',
    ];

    protected $casts = [];

    public function grupo() { return $this->belongsTo(\App\Modules\Grupos\Models\Grupo::class); }
    public function materia() { return $this->belongsTo(\App\Modules\Materias\Models\Materia::class); }
    public function docente() { return $this->belongsTo(\App\Modules\Docentes\Models\Docente::class); }
}
