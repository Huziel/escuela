<?php
namespace Database\Seeders;

use App\Models\User;
use App\Modules\Alumnos\Models\Alumno;
use App\Modules\Especialidades\Models\Especialidad;
use App\Modules\Grupos\Models\Grupo;
use App\Modules\Tutores\Models\Tutor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class AlumnoSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_MX');
        $especialidades = Especialidad::all();
        $grupos = Grupo::all();
        $tutores = Tutor::all();

        for ($i = 1; $i <= 50; $i++) {
            $esp = $especialidades->random();
            $semestre = rand(1, 6);
            $gruposSemestre = $grupos->where('especialidad_id', $esp->id)->where('semestre', $semestre);
            $grupo = $gruposSemestre->isNotEmpty() ? $gruposSemestre->random() : $grupos->random();

            $user = User::create([
                'username' => 'alumno'.$i,
                'nombre' => $faker->firstName,
                'apellido_paterno' => $faker->lastName,
                'apellido_materno' => $faker->lastName,
                'email' => 'alumno'.$i.'@sice.edu.mx',
                'password' => Hash::make('password123'),
                'sexo' => $faker->randomElement(['M','F']),
                'rol' => 'alumno',
                'activo' => true,
                'telefono' => $faker->phoneNumber,
                'direccion' => $faker->address,
                'fecha_nacimiento' => $faker->date('Y-m-d', '-17 years'),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('alumno');

            Alumno::create([
                'user_id' => $user->id,
                'matricula' => 'SICE'.str_pad($i, 5, '0', STR_PAD_LEFT),
                'curp' => strtoupper($faker->bothify('????######????##')),
                'especialidad_id' => $esp->id,
                'semestre' => $semestre,
                'grupo_id' => $grupo->id,
                'tutor_id' => $tutores->random()->id,
                'estatus' => $faker->randomElement(['activo','activo','activo','activo','egresado']),
                'fecha_ingreso' => $faker->date('Y-m-d', '-3 years'),
            ]);
        }
    }
}
