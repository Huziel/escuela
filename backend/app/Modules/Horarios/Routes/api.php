<?php
use App\Modules\Horarios\Controllers\HorarioController;
use Illuminate\Support\Facades\Route;
Route::prefix('horarios')->group(function(){
    Route::get('/',[HorarioController::class,'index'])->middleware('permission:horarios.listar');
    Route::get('/resumen',[HorarioController::class,'resumen'])->middleware('permission:horarios.listar');
    Route::get('/docentes-por-materia',[HorarioController::class,'docentesPorMateria'])->middleware('permission:horarios.listar');
    Route::post('/',[HorarioController::class,'store'])->middleware('permission:horarios.crear');
    Route::get('/{id}',[HorarioController::class,'show'])->middleware('permission:horarios.listar');
    Route::put('/{id}',[HorarioController::class,'update'])->middleware('permission:horarios.editar');
    Route::delete('/{id}',[HorarioController::class,'destroy'])->middleware('permission:horarios.eliminar');
});
