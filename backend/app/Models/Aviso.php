<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aviso extends Model
{
    protected $table = 'avisos';

    protected $fillable = ['user_id', 'titulo', 'contenido', 'tipo', 'grupo_id', 'fecha_inicio', 'fecha_fin', 'activo'];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function grupo() { return $this->belongsTo(\App\Modules\Grupos\Models\Grupo::class); }
}
