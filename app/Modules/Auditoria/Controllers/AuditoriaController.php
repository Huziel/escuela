<?php
namespace App\Modules\Auditoria\Controllers;
use App\Http\Controllers\Controller; use App\Traits\ApiResponse;
use App\Modules\Auditoria\Models\Auditoria;
use Illuminate\Http\JsonResponse; use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Auditoria::with('user');
        if ($request->filled('user_id')) $query->where('user_id',$request->user_id);
        if ($request->filled('accion')) $query->where('accion',$request->accion);
        if ($request->filled('tabla')) $query->where('tabla',$request->tabla);
        if ($request->filled('fecha_desde')) $query->whereDate('created_at','>=',$request->fecha_desde);
        if ($request->filled('fecha_hasta')) $query->whereDate('created_at','<=',$request->fecha_hasta);
        return $this->paginated($query->latest()->paginate($request->get('per_page',50)), 'Auditoría.');
    }
}
