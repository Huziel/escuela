<?php
use App\Modules\Usuarios\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;
Route::prefix('usuarios')->group(function(){
    Route::get('/permisos-disponibles',[UsuarioController::class,'allPermissions'])->middleware('permission:usuarios.listar');
    Route::get('/',[UsuarioController::class,'index'])->middleware('permission:usuarios.listar');
    Route::post('/',[UsuarioController::class,'store'])->middleware('permission:usuarios.crear');
    Route::get('/{id}',[UsuarioController::class,'show'])->middleware('permission:usuarios.listar');
    Route::put('/{id}',[UsuarioController::class,'update'])->middleware('permission:usuarios.editar');
    Route::delete('/{id}',[UsuarioController::class,'destroy'])->middleware('permission:usuarios.eliminar');
    Route::post('/{id}/bloquear',[UsuarioController::class,'bloquear'])->middleware('permission:usuarios.bloquear');
    Route::post('/{id}/desbloquear',[UsuarioController::class,'desbloquear'])->middleware('permission:usuarios.bloquear');
    Route::post('/{id}/foto',[UsuarioController::class,'uploadFoto'])->middleware('permission:usuarios.editar');
    Route::get('/{id}/bitacora',[UsuarioController::class,'bitacora'])->middleware('permission:usuarios.listar');
    Route::get('/{id}/permisos',[UsuarioController::class,'permissions'])->middleware('permission:usuarios.editar');
    Route::put('/{id}/permisos',[UsuarioController::class,'syncPermissions'])->middleware('permission:usuarios.editar');
});
