<?php
namespace Database\Seeders;

use App\Models\User;
use App\Modules\Tutores\Models\Tutor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class TutorSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_MX');
        $parentescos = ['padre','madre','tutor','otro'];

        for ($i = 1; $i <= 35; $i++) {
            $user = User::create([
                'username' => 'tutor'.$i,
                'nombre' => $faker->firstName,
                'apellido_paterno' => $faker->lastName,
                'apellido_materno' => $faker->lastName,
                'email' => 'tutor'.$i.'@email.com',
                'password' => Hash::make('password123'),
                'sexo' => $faker->randomElement(['M','F']),
                'rol' => 'tutor',
                'activo' => true,
                'telefono' => $faker->phoneNumber,
                'direccion' => $faker->address,
                'email_verified_at' => now(),
            ]);
            $user->assignRole('tutor');

            Tutor::create([
                'user_id' => $user->id,
                'parentesco' => $faker->randomElement($parentescos),
                'ocupacion' => $faker->jobTitle,
                'direccion' => $faker->address,
                'telefono_trabajo' => $faker->phoneNumber,
            ]);
        }
    }
}
