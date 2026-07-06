<?php
use App\Modules\Docentes\Controllers\DocenteController;
use Illuminate\Support\Facades\Route;
Route::prefix('docentes')->group(function(){
    Route::get('/',[DocenteController::class,'index'])->middleware('permission:docentes.listar');
    Route::get('/all',[DocenteController::class,'listAll'])->middleware('permission:docentes.listar');
    Route::post('/',[DocenteController::class,'store'])->middleware('permission:docentes.crear');
    Route::get('/{id}',[DocenteController::class,'show'])->middleware('permission:docentes.listar');
    Route::put('/{id}',[DocenteController::class,'update'])->middleware('permission:docentes.editar');
    Route::delete('/{id}',[DocenteController::class,'destroy'])->middleware('permission:docentes.eliminar');
});
