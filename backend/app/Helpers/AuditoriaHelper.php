<?php namespace App\Helpers;
use App\Modules\Auditoria\Models\Auditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditoriaHelper
{
    public static function registrar(string $accion, string $tabla = null, $registroId = null, $anterior = null, $nuevo = null): void
    {
        $userId = Auth::id();
        Auditoria::create([
            'user_id' => $userId,
            'usuario_nombre' => Auth::user()?->nombre_completo ?? 'Sistema',
            'ip' => Request::ip(),
            'accion' => $accion,
            'tabla' => $tabla,
            'registro_id' => $registroId,
            'registro_anterior' => $anterior ? json_encode($anterior) : null,
            'registro_nuevo' => $nuevo ? json_encode($nuevo) : null,
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }
}
