<?php
use App\Modules\Auditoria\Controllers\AuditoriaController;
use Illuminate\Support\Facades\Route;
Route::prefix('auditoria')->group(function(){
    Route::get('/',[AuditoriaController::class,'index'])->middleware('permission:auditoria.ver');
});
