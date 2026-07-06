<?php
use App\Modules\Asistencia\Controllers\AsistenciaController;
use Illuminate\Support\Facades\Route;
Route::prefix('asistencia')->group(function(){
    Route::get('/',[AsistenciaController::class,'index'])->middleware('permission:asistencia.listar');
    Route::post('/',[AsistenciaController::class,'store'])->middleware('permission:asistencia.registrar');
    Route::post('/pase-lista',[AsistenciaController::class,'paseLista'])->middleware('permission:asistencia.registrar');
    Route::get('/{id}',[AsistenciaController::class,'show'])->middleware('permission:asistencia.listar');
    Route::put('/{id}',[AsistenciaController::class,'update'])->middleware('permission:asistencia.registrar');
    Route::delete('/{id}',[AsistenciaController::class,'destroy'])->middleware('permission:asistencia.registrar');
    Route::post('/{id}/justificar',[AsistenciaController::class,'justificar'])->middleware('permission:asistencia.justificar');
});
