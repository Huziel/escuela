<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
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
    }
}
