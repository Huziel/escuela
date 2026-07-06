<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'usuarios.listar','usuarios.crear','usuarios.editar','usuarios.eliminar','usuarios.bloquear',
            'alumnos.listar','alumnos.crear','alumnos.editar','alumnos.eliminar','alumnos.importar','alumnos.exportar','alumnos.bloquear',
            'docentes.listar','docentes.crear','docentes.editar','docentes.eliminar',
            'grupos.listar','grupos.crear','grupos.editar','grupos.eliminar',
            'especialidades.listar','especialidades.crear','especialidades.editar','especialidades.eliminar',
            'materias.listar','materias.crear','materias.editar','materias.eliminar',
            'inscripciones.listar','inscripciones.crear','inscripciones.aprobar','inscripciones.rechazar',
            'calificaciones.listar','calificaciones.crear','calificaciones.editar','calificaciones.exportar',
            'asistencia.listar','asistencia.registrar','asistencia.justificar',
            'tutores.listar','tutores.crear','tutores.editar','tutores.eliminar',
            'regularizacion.listar','regularizacion.crear','regularizacion.editar',
            'reportes.ver','reportes.exportar','reportes.kardex','reportes.boletas',
            'configuracion.ver','configuracion.editar',
            'auditoria.ver',
            'dashboard.ver',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

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
            'alumnos.listar','calificaciones.listar','calificaciones.crear','calificaciones.editar',
            'asistencia.listar','asistencia.registrar','asistencia.justificar','dashboard.ver',
        ])->get());

        $tutorRole = Role::firstOrCreate(['name' => 'tutor', 'guard_name' => 'web']);
        $tutorRole->syncPermissions(Permission::whereIn('name', [
            'alumnos.listar','calificaciones.listar','asistencia.listar',
            'reportes.ver','reportes.kardex','reportes.boletas','dashboard.ver',
        ])->get());

        $alumnoRole = Role::firstOrCreate(['name' => 'alumno', 'guard_name' => 'web']);
        $alumnoRole->syncPermissions(Permission::whereIn('name', [
            'calificaciones.listar','asistencia.listar','reportes.ver','dashboard.ver',
        ])->get());
    }
}
