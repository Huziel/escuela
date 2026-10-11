<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Acceso extends Model
{
    protected $table = 'accesos';

    public $timestamps = false;

    protected $fillable = ['user_id', 'ip', 'user_agent', 'tipo', 'exito', 'created_at'];

    protected $casts = [
        'exito' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
}
