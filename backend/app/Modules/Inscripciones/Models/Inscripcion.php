<?php

namespace App\Modules\Inscripciones\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inscripcion extends Model
{
    use SoftDeletes;

    protected $table = 'inscripciones';

    protected $fillable = [
        'alumno_id', 'grupo_id', 'tipo', 'periodo', 'estatus',
        'fecha_solicitud', 'fecha_resolucion', 'documentos_completos', 'observaciones',
    ];

    protected $casts = [
        'fecha_solicitud' => 'date',
        'fecha_resolucion' => 'date',
        'documentos_completos' => 'boolean',
    ];

    public function alumno() { return $this->belongsTo(\App\Modules\Alumnos\Models\Alumno::class); }
    public function grupo() { return $this->belongsTo(\App\Modules\Grupos\Models\Grupo::class); }
    public function materias() { return $this->belongsToMany(\App\Modules\Materias\Models\Materia::class, 'inscripcion_materia'); }
}
