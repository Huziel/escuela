<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    protected $fillable = ['nombre', 'edificio', 'capacidad', 'activo'];

    protected $casts = ['activo' => 'boolean', 'capacidad' => 'integer'];

    public function grupos()
    {
        return $this->hasMany(\App\Modules\Grupos\Models\Grupo::class);
    }
}
