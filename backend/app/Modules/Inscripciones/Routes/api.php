<?php
use App\Modules\Inscripciones\Controllers\InscripcionController;
use Illuminate\Support\Facades\Route;
Route::prefix('inscripciones')->group(function(){
    Route::get('/',[InscripcionController::class,'index'])->middleware('permission:inscripciones.listar');
    Route::post('/',[InscripcionController::class,'store'])->middleware('permission:inscripciones.crear');
    Route::get('/{id}',[InscripcionController::class,'show'])->middleware('permission:inscripciones.listar');
    Route::put('/{id}',[InscripcionController::class,'update'])->middleware('permission:inscripciones.editar');
    Route::delete('/{id}',[InscripcionController::class,'destroy'])->middleware('permission:inscripciones.eliminar');
    Route::post('/{id}/aprobar',[InscripcionController::class,'aprobar'])->middleware('permission:inscripciones.aprobar');
    Route::post('/{id}/rechazar',[InscripcionController::class,'rechazar'])->middleware('permission:inscripciones.rechazar');
});
