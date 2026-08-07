<?php
use App\Modules\Calificaciones\Controllers\CalificacionController;
use Illuminate\Support\Facades\Route;
Route::prefix('calificaciones')->group(function(){
    Route::get('/',[CalificacionController::class,'index'])->middleware('permission:calificaciones.listar');
    Route::post('/',[CalificacionController::class,'store'])->middleware('permission:calificaciones.crear');
    Route::get('/{id}',[CalificacionController::class,'show'])->middleware('permission:calificaciones.listar');
    Route::put('/{id}',[CalificacionController::class,'update'])->middleware('permission:calificaciones.editar');
    Route::delete('/{id}',[CalificacionController::class,'destroy'])->middleware('permission:calificaciones.eliminar');
    Route::get('/kardex/{alumnoId}',[CalificacionController::class,'kardex'])->middleware('permission:calificaciones.listar');
    Route::get('/grupo/{grupoId}/materia/{materiaId}',[CalificacionController::class,'grupoMateria'])->middleware('permission:calificaciones.listar');
    Route::post('/guardar-lote',[CalificacionController::class,'guardarLote'])->middleware('permission:calificaciones.editar');
    Route::post('/sincronizar-grupo/{grupoId}/materia/{materiaId}',[CalificacionController::class,'sincronizarGrupoMateria'])->middleware('permission:calificaciones.editar');
});
