<?php
use App\Modules\PortalAlumno\Controllers\PortalAlumnoController;
use Illuminate\Support\Facades\Route;

Route::prefix('portal-alumno')->middleware(['auth:sanctum', 'role:alumno'])->group(function () {
    Route::get('/dashboard', [PortalAlumnoController::class, 'dashboard']);
    Route::get('/perfil', [PortalAlumnoController::class, 'perfil']);
    Route::get('/calificaciones', [PortalAlumnoController::class, 'calificaciones']);
    Route::get('/kardex', [PortalAlumnoController::class, 'kardex']);
    Route::get('/asistencias', [PortalAlumnoController::class, 'asistencias']);
    Route::get('/horario', [PortalAlumnoController::class, 'horario']);
    Route::get('/boleta', [PortalAlumnoController::class, 'boleta']);
    Route::post('/change-password', [PortalAlumnoController::class, 'changePassword']);
});
