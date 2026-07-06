<?php
namespace Database\Seeders;

use App\Models\User;
use App\Modules\Docentes\Models\Docente;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class DocenteSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_MX');
        $especialidades = ['Programación','Matemáticas','Física','Química','Inglés','Historia','Electrónica','Administración'];
        $grados = ['Licenciatura','Maestría','Doctorado','Ingeniería'];

        for ($i = 1; $i <= 15; $i++) {
            $user = User::create([
                'username' => 'docente'.$i,
                'nombre' => $faker->firstName,
                'apellido_paterno' => $faker->lastName,
                'apellido_materno' => $faker->lastName,
                'email' => 'docente'.$i.'@sice.edu.mx',
                'password' => Hash::make('password123'),
                'sexo' => $faker->randomElement(['M','F']),
                'rol' => 'docente',
                'activo' => true,
                'telefono' => $faker->phoneNumber,
                'direccion' => $faker->address,
                'fecha_nacimiento' => $faker->date('Y-m-d', '-30 years'),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('docente');

            Docente::create([
                'user_id' => $user->id,
                'numero_empleado' => 'EMP'.str_pad($i, 4, '0', STR_PAD_LEFT),
                'curp' => strtoupper($faker->bothify('????######????##')),
                'especialidad' => $faker->randomElement($especialidades),
                'grado_academico' => $faker->randomElement($grados),
                'rfc' => strtoupper($faker->bothify('????######???')),
                'estatus' => 'activo',
                'fecha_ingreso' => $faker->date('Y-m-d', '-5 years'),
            ]);
        }
    }
}
