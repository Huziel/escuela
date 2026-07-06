<?php
use App\Modules\Regularizacion\Controllers\RegularizacionController;
use Illuminate\Support\Facades\Route;
Route::prefix('regularizacion')->group(function(){
    Route::get('/',[RegularizacionController::class,'index'])->middleware('permission:regularizacion.listar');
    Route::post('/',[RegularizacionController::class,'store'])->middleware('permission:regularizacion.crear');
    Route::get('/{id}',[RegularizacionController::class,'show'])->middleware('permission:regularizacion.listar');
    Route::put('/{id}',[RegularizacionController::class,'update'])->middleware('permission:regularizacion.editar');
    Route::delete('/{id}',[RegularizacionController::class,'destroy'])->middleware('permission:regularizacion.editar');
});
