<?php
use App\Modules\Configuracion\Controllers\ConfiguracionController;
use Illuminate\Support\Facades\Route;
Route::prefix('configuracion')->group(function(){
    Route::get('/',[ConfiguracionController::class,'index'])->middleware('permission:configuracion.ver');
    Route::put('/',[ConfiguracionController::class,'update'])->middleware('permission:configuracion.editar');
    Route::get('/noticias',[ConfiguracionController::class,'noticias']);
    Route::post('/noticias',[ConfiguracionController::class,'storeNoticia'])->middleware('permission:configuracion.editar');
    Route::put('/noticias/{id}',[ConfiguracionController::class,'updateNoticia'])->middleware('permission:configuracion.editar');
    Route::delete('/noticias/{id}',[ConfiguracionController::class,'destroyNoticia'])->middleware('permission:configuracion.editar');
    Route::get('/avisos',[ConfiguracionController::class,'avisos']);
    Route::post('/avisos',[ConfiguracionController::class,'storeAviso'])->middleware('permission:configuracion.editar');
    Route::put('/avisos/{id}',[ConfiguracionController::class,'updateAviso'])->middleware('permission:configuracion.editar');
    Route::delete('/avisos/{id}',[ConfiguracionController::class,'destroyAviso'])->middleware('permission:configuracion.editar');
    Route::get('/ciclos',[ConfiguracionController::class,'ciclos']);
    Route::post('/ciclos',[ConfiguracionController::class,'storeCiclo'])->middleware('permission:configuracion.editar');
    Route::put('/ciclos/{id}',[ConfiguracionController::class,'updateCiclo'])->middleware('permission:configuracion.editar');
    Route::delete('/ciclos/{id}',[ConfiguracionController::class,'destroyCiclo'])->middleware('permission:configuracion.editar');
});
