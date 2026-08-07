<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\AulaController;
use App\Http\Controllers\Api\V1\PlanEstudioController;
use App\Http\Controllers\Api\V1\Import\ImportController;
use App\Http\Controllers\Api\V1\GeneracionController;

Route::prefix('v1')->group(function () {
    // Auth - public
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1');
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // Auth - authenticated
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);

        // Dashboard
        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
        Route::get('/dashboard/charts', [DashboardController::class, 'charts']);

        Route::get('/aulas/list', [AulaController::class, 'listAll']);
        Route::apiResource('/aulas', AulaController::class)->except(['show']);

        Route::get('/generaciones/all', [GeneracionController::class, 'all']);
        Route::apiResource('/generaciones', GeneracionController::class);

        Route::get('/import/template/{type}', [ImportController::class, 'template']);
        Route::post('/import/preview/{type}', [ImportController::class, 'preview']);
        Route::post('/import/process/{type}', [ImportController::class, 'process']);

        Route::get('/planes-estudio', [PlanEstudioController::class, 'index']);
        Route::post('/planes-estudio', [PlanEstudioController::class, 'store']);
        Route::put('/planes-estudio/{id}', [PlanEstudioController::class, 'update']);
        Route::delete('/planes-estudio/{id}', [PlanEstudioController::class, 'destroy']);
        Route::post('/planes-estudio/{id}/materias', [PlanEstudioController::class, 'syncMaterias']);

        // Module routes
        require base_path('app/Modules/Usuarios/Routes/api.php');
        require base_path('app/Modules/Alumnos/Routes/api.php');
        require base_path('app/Modules/Docentes/Routes/api.php');
        require base_path('app/Modules/Grupos/Routes/api.php');
        require base_path('app/Modules/Especialidades/Routes/api.php');
        require base_path('app/Modules/Materias/Routes/api.php');
        require base_path('app/Modules/Inscripciones/Routes/api.php');
        require base_path('app/Modules/Calificaciones/Routes/api.php');
        require base_path('app/Modules/Asistencia/Routes/api.php');
        require base_path('app/Modules/Tutores/Routes/api.php');
        require base_path('app/Modules/Regularizacion/Routes/api.php');
        require base_path('app/Modules/Reportes/Routes/api.php');
        require base_path('app/Modules/Notificaciones/Routes/api.php');
        require base_path('app/Modules/Auditoria/Routes/api.php');
        require base_path('app/Modules/Configuracion/Routes/api.php');
        require base_path('app/Modules/Horarios/Routes/api.php');
        require base_path('app/Modules/PortalAlumno/Routes/api.php');
    });
});

Route::fallback(function () {
    return response()->json(['success' => false, 'message' => 'Ruta no encontrada.'], 404);
});
