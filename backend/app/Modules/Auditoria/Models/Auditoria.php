<?php

namespace App\Modules\Auditoria\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'auditoria';

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'usuario_nombre', 'ip', 'accion', 'tabla',
        'registro_id', 'registro_anterior', 'registro_nuevo', 'user_agent', 'created_at',
    ];

    protected $casts = [
        'registro_anterior' => 'json',
        'registro_nuevo' => 'json',
        'created_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(\App\Models\User::class); }
}
