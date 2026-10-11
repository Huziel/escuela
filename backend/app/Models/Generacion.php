<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Generacion extends Model
{
    use SoftDeletes;

    protected $table = 'generaciones';
    protected $fillable = ['nombre', 'fecha_inicio', 'fecha_termino', 'descripcion', 'activo'];
    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_termino' => 'date',
        'activo' => 'boolean',
    ];

    public function alumnos()
    {
        return $this->hasMany(\App\Modules\Alumnos\Models\Alumno::class);
    }
}
