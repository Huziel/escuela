<?php namespace App\Providers;
use Illuminate\Support\ServiceProvider;

class ModulesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register module-specific bindings
    }
    
    public function boot(): void
    {
        // Load module routes
        $modules = ['Usuarios','Dashboard','Alumnos','Docentes','Grupos','Especialidades','Materias','Inscripciones','Calificaciones','Asistencia','Tutores','Regularizacion','Reportes','Notificaciones','Auditoria','Configuracion'];
        foreach ($modules as $module) {
            $routeFile = app_path("Modules/{$module}/Routes/api.php");
            if (file_exists($routeFile)) {
                $this->loadRoutesFrom($routeFile);
            }
        }
    }
}
