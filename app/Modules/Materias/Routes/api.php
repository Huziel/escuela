<?php
use App\Modules\Materias\Controllers\MateriaController;
use Illuminate\Support\Facades\Route;
Route::prefix('materias')->group(function(){
    Route::get('/',[MateriaController::class,'index'])->middleware('permission:materias.listar');
    Route::get('/all',[MateriaController::class,'listAll'])->middleware('permission:materias.listar');
    Route::post('/',[MateriaController::class,'store'])->middleware('permission:materias.crear');
    Route::get('/{id}',[MateriaController::class,'show'])->middleware('permission:materias.listar');
    Route::put('/{id}',[MateriaController::class,'update'])->middleware('permission:materias.editar');
    Route::delete('/{id}',[MateriaController::class,'destroy'])->middleware('permission:materias.eliminar');
});
