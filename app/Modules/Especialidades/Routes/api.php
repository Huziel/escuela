<?php
use App\Modules\Especialidades\Controllers\EspecialidadController;
use Illuminate\Support\Facades\Route;
Route::prefix('especialidades')->group(function(){
    Route::get('/',[EspecialidadController::class,'index'])->middleware('permission:especialidades.listar');
    Route::get('/all',[EspecialidadController::class,'listAll'])->middleware('permission:especialidades.listar');
    Route::post('/',[EspecialidadController::class,'store'])->middleware('permission:especialidades.crear');
    Route::get('/{id}',[EspecialidadController::class,'show'])->middleware('permission:especialidades.listar');
    Route::put('/{id}',[EspecialidadController::class,'update'])->middleware('permission:especialidades.editar');
    Route::delete('/{id}',[EspecialidadController::class,'destroy'])->middleware('permission:especialidades.eliminar');
});
