<?php namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Run only if roles table exists
        if (!app()->runningInConsole() || !\Illuminate\Support\Facades\Schema::hasTable('roles')) {
            return;
        }
        
        $permissions = [
            // Usuarios
            'usuarios.listar','usuarios.crear','usuarios.editar','usuarios.eliminar','usuarios.bloquear',
            // Alumnos
            'alumnos.listar','alumnos.crear','alumnos.editar','alumnos.eliminar',
            'alumnos.importar','alumnos.exportar','alumnos.bloquear',
            // Docentes
            'docentes.listar','docentes.crear','docentes.editar','docentes.eliminar',
            // Grupos
            'grupos.listar','grupos.crear','grupos.editar','grupos.eliminar',
            // Especialidades
            'especialidades.listar','especialidades.crear','especialidades.editar','especialidades.eliminar',
            // Materias
            'materias.listar','materias.crear','materias.editar','materias.eliminar',
            // Inscripciones
            'inscripciones.listar','inscripciones.crear','inscripciones.aprobar','inscripciones.rechazar',
            // Calificaciones
            'calificaciones.listar','calificaciones.crear','calificaciones.editar','calificaciones.exportar',
            // Asistencia
            'asistencia.listar','asistencia.registrar','asistencia.justificar',
            // Tutores
            'tutores.listar','tutores.crear','tutores.editar','tutores.eliminar',
            // Regularizacion
            'regularizacion.listar','regularizacion.crear','regularizacion.editar',
            // Reportes
            'reportes.ver','reportes.exportar','reportes.kardex','reportes.boletas',
            // Configuracion
            'configuracion.ver','configuracion.editar',
            // Auditoria
            'auditoria.ver',
            // Dashboard
            'dashboard.ver',
        ];
        
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        
        // Create roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());
        
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());
        
        $controlEscolar = Role::firstOrCreate(['name' => 'control_escolar', 'guard_name' => 'web']);
        $controlEscolar->syncPermissions(Permission::whereIn('name', [
            'alumnos.listar','alumnos.crear','alumnos.editar','alumnos.importar','alumnos.exportar',
            'inscripciones.listar','inscripciones.crear','inscripciones.aprobar','inscripciones.rechazar',
            'calificaciones.listar','calificaciones.editar',
            'asistencia.listar','asistencia.registrar','asistencia.justificar',
            'reportes.ver','reportes.exportar','reportes.kardex','reportes.boletas',
            'dashboard.ver',
        ])->get());
        
        $docenteRole = Role::firstOrCreate(['name' => 'docente', 'guard_name' => 'web']);
        $docenteRole->syncPermissions(Permission::whereIn('name', [
            'alumnos.listar',
            'calificaciones.listar','calificaciones.crear','calificaciones.editar',
            'asistencia.listar','asistencia.registrar','asistencia.justificar',
            'dashboard.ver',
        ])->get());
        
        $tutorRole = Role::firstOrCreate(['name' => 'tutor', 'guard_name' => 'web']);
        $tutorRole->syncPermissions(Permission::whereIn('name', [
            'alumnos.listar',
            'calificaciones.listar',
            'asistencia.listar',
            'reportes.ver','reportes.kardex','reportes.boletas',
            'dashboard.ver',
        ])->get());
        
        $alumnoRole = Role::firstOrCreate(['name' => 'alumno', 'guard_name' => 'web']);
        $alumnoRole->syncPermissions(Permission::whereIn('name', [
            'calificaciones.listar',
            'asistencia.listar',
            'reportes.ver',
            'dashboard.ver',
        ])->get());
    }
}
