<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PlanEstudio extends Model
{
    protected $table = 'planes_estudio';
    protected $fillable = ['especialidad_id', 'nombre', 'descripcion', 'activo'];
    protected $casts = ['activo' => 'boolean'];
    public function especialidad() { return $this->belongsTo(\App\Modules\Especialidades\Models\Especialidad::class); }
    public function materias() { return $this->belongsToMany(\App\Modules\Materias\Models\Materia::class, 'plan_materia', 'plan_id', 'materia_id')->withPivot('semestre', 'horas'); }
}
