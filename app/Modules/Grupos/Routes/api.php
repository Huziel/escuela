<?php
use App\Modules\Grupos\Controllers\GrupoController;
use Illuminate\Support\Facades\Route;
Route::prefix('grupos')->group(function(){
    Route::get('/',[GrupoController::class,'index'])->middleware('permission:grupos.listar');
    Route::get('/all',[GrupoController::class,'listAll'])->middleware('permission:grupos.listar');
    Route::post('/',[GrupoController::class,'store'])->middleware('permission:grupos.crear');
    Route::post('/verificar-horario',[GrupoController::class,'verificarHorario'])->middleware('permission:grupos.listar');
    Route::get('/{id}',[GrupoController::class,'show'])->middleware('permission:grupos.listar');
    Route::put('/{id}',[GrupoController::class,'update'])->middleware('permission:grupos.editar');
    Route::delete('/{id}',[GrupoController::class,'destroy'])->middleware('permission:grupos.eliminar');
    Route::get('/{id}/docentes',[GrupoController::class,'docentes'])->middleware('permission:grupos.listar');
    Route::post('/{id}/docentes',[GrupoController::class,'asignarDocente'])->middleware('permission:grupos.editar');
    Route::delete('/{id}/docentes/{docente_id}',[GrupoController::class,'removerDocente'])->middleware('permission:grupos.editar');
});
