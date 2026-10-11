<?php
use App\Modules\Tutores\Controllers\TutorController;
use Illuminate\Support\Facades\Route;
Route::prefix('tutores')->group(function(){
    Route::get('/',[TutorController::class,'index'])->middleware('permission:tutores.listar');
    Route::get('/all',[TutorController::class,'listAll'])->middleware('permission:tutores.listar');
    Route::post('/',[TutorController::class,'store'])->middleware('permission:tutores.crear');
    Route::get('/{id}',[TutorController::class,'show'])->middleware('permission:tutores.listar');
    Route::put('/{id}',[TutorController::class,'update'])->middleware('permission:tutores.editar');
    Route::delete('/{id}',[TutorController::class,'destroy'])->middleware('permission:tutores.eliminar');
});
