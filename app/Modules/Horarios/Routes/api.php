<?php
use App\Modules\Horarios\Controllers\HorarioController;
use Illuminate\Support\Facades\Route;
Route::prefix('horarios')->group(function(){
    Route::get('/',[HorarioController::class,'index'])->middleware('auth:sanctum');
    Route::get('/resumen',[HorarioController::class,'resumen'])->middleware('auth:sanctum');
    Route::get('/docentes-por-materia',[HorarioController::class,'docentesPorMateria'])->middleware('auth:sanctum');
    Route::post('/',[HorarioController::class,'store'])->middleware('auth:sanctum');
    Route::get('/{id}',[HorarioController::class,'show'])->middleware('auth:sanctum');
    Route::put('/{id}',[HorarioController::class,'update'])->middleware('auth:sanctum');
    Route::delete('/{id}',[HorarioController::class,'destroy'])->middleware('auth:sanctum');
});
