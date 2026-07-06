<?php
use App\Modules\Notificaciones\Controllers\NotificacionController;
use Illuminate\Support\Facades\Route;
Route::prefix('notificaciones')->group(function(){
    Route::get('/',[NotificacionController::class,'index']);
    Route::get('/no-leidas',[NotificacionController::class,'noLeidas']);
    Route::post('/{id}/leida',[NotificacionController::class,'marcarLeida']);
    Route::post('/marcar-todas',[NotificacionController::class,'marcarTodasLeidas']);
});
