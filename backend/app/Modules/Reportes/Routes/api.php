<?php
use App\Modules\Reportes\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;
Route::prefix('reportes')->group(function(){
    Route::get('/kardex',[ReporteController::class,'kardex'])->middleware('permission:reportes.kardex');
    Route::get('/kardex/pdf',[ReporteController::class,'kardexPdf'])->middleware('permission:reportes.kardex');
    Route::get('/boleta',[ReporteController::class,'boleta'])->middleware('permission:reportes.boletas');
    Route::get('/asistencia',[ReporteController::class,'asistencia'])->middleware('permission:reportes.ver');
    Route::get('/indicadores',[ReporteController::class,'indicadores'])->middleware('permission:reportes.ver');
});
