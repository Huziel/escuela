<?php
use App\Modules\Alumnos\Controllers\AlumnoController;
use Illuminate\Support\Facades\Route;

Route::prefix('alumnos')->group(function () {
    Route::get('/', [AlumnoController::class, 'index'])->middleware('permission:alumnos.listar');
    Route::post('/', [AlumnoController::class, 'store'])->middleware('permission:alumnos.crear');
    Route::get('/{id}', [AlumnoController::class, 'show'])->middleware('permission:alumnos.listar');
    Route::put('/{id}', [AlumnoController::class, 'update'])->middleware('permission:alumnos.editar');
    Route::delete('/{id}', [AlumnoController::class, 'destroy'])->middleware('permission:alumnos.eliminar');
    Route::post('/{id}/bloquear', [AlumnoController::class, 'bloquear'])->middleware('permission:alumnos.bloquear');
    Route::post('/{id}/desbloquear', [AlumnoController::class, 'desbloquear'])->middleware('permission:alumnos.bloquear');
    Route::get('/{id}/historial', [AlumnoController::class, 'historial'])->middleware('permission:alumnos.listar');
});
