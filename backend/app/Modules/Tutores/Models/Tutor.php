<?php

namespace App\Modules\Tutores\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tutor extends Model
{
    use SoftDeletes;

    protected $table = 'tutores';

    protected $fillable = ['user_id', 'parentesco', 'ocupacion', 'direccion', 'telefono_trabajo'];

    public function user() { return $this->belongsTo(\App\Models\User::class); }
    public function alumnos() { return $this->hasMany(\App\Modules\Alumnos\Models\Alumno::class); }
}
