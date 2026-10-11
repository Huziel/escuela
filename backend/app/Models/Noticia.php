<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Noticia extends Model
{
    use SoftDeletes;

    protected $table = 'noticias';

    protected $fillable = ['user_id', 'titulo', 'contenido', 'imagen', 'fecha_publicacion', 'activo'];

    protected $casts = [
        'fecha_publicacion' => 'date',
        'activo' => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class); }
}
