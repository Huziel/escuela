<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'username', 'nombre', 'apellido_paterno', 'apellido_materno',
        'email', 'password', 'curp', 'sexo', 'fecha_nacimiento',
        'telefono', 'direccion', 'fotografia', 'rol',
        'activo', 'ultimo_acceso', 'ip_ultimo_acceso',
        'two_factor_enabled', 'two_factor_secret', 'email_notifications',
    ];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'fecha_nacimiento' => 'date',
        'ultimo_acceso' => 'datetime',
        'activo' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'email_notifications' => 'boolean',
    ];

    protected $appends = ['nombre_completo'];

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->apellido_paterno} {$this->apellido_materno}");
    }

    public function alumno() { return $this->hasOne(\App\Modules\Alumnos\Models\Alumno::class); }
    public function docente() { return $this->hasOne(\App\Modules\Docentes\Models\Docente::class); }
    public function tutor() { return $this->hasOne(\App\Modules\Tutores\Models\Tutor::class); }
    public function notificaciones() { return $this->hasMany(\App\Modules\Notificaciones\Models\Notificacion::class); }
    public function accesos() { return $this->hasMany(\App\Models\Acceso::class); }
    public function noticias() { return $this->hasMany(\App\Models\Noticia::class); }
}
