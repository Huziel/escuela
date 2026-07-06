<?php
namespace App\Modules\Notificaciones\Controllers;
use App\Http\Controllers\Controller; use App\Traits\ApiResponse;
use App\Modules\Notificaciones\Models\Notificacion;
use Illuminate\Http\JsonResponse; use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Notificacion::where('user_id', auth()->id());
        if ($request->has('leida')) $query->where('leida', $request->leida);
        return $this->paginated($query->latest()->paginate($request->get('per_page',20)), 'Notificaciones.');
    }

    public function marcarLeida(int $id): JsonResponse
    {
        Notificacion::where('id',$id)->where('user_id',auth()->id())->update(['leida'=>true]);
        return $this->success(null,'Marcada como leída.');
    }

    public function marcarTodasLeidas(): JsonResponse
    {
        Notificacion::where('user_id',auth()->id())->where('leida',false)->update(['leida'=>true]);
        return $this->success(null,'Todas marcadas como leídas.');
    }

    public function noLeidas(): JsonResponse
    {
        $count = Notificacion::where('user_id',auth()->id())->where('leida',false)->count();
        $notificaciones = Notificacion::where('user_id',auth()->id())->where('leida',false)->latest()->take(10)->get();
        return $this->success(['count'=>$count,'notificaciones'=>$notificaciones],'No leídas.');
    }
}
