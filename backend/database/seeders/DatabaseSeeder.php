<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            EspecialidadSeeder::class,
            TutorSeeder::class,
            DocenteSeeder::class,
            MateriaSeeder::class,
            GrupoSeeder::class,
            AlumnoSeeder::class,
            InscripcionSeeder::class,
            CalificacionSeeder::class,
            AsistenciaSeeder::class,
            ConfiguracionSeeder::class,
            NoticiaSeeder::class,
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
