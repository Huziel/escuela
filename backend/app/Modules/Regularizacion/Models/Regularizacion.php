<?php

namespace App\Modules\Regularizacion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Regularizacion extends Model
{
    use SoftDeletes;

    protected $table = 'regularizaciones';

    protected $fillable = [
        'alumno_id', 'materia_id', 'calificacion_id', 'periodo',
        'fecha_examen', 'calificacion', 'estatus', 'pago', 'pagado', 'observaciones',
    ];

    protected $casts = [
        'fecha_examen' => 'date',
        'pagado' => 'boolean',
    ];

    public function alumno() { return $this->belongsTo(\App\Modules\Alumnos\Models\Alumno::class); }
    public function materia() { return $this->belongsTo(\App\Modules\Materias\Models\Materia::class); }
    public function calificacion() { return $this->belongsTo(\App\Modules\Calificaciones\Models\Calificacion::class); }
}
