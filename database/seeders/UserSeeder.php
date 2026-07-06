<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        $sa = User::create([
            'username' => 'admin',
            'nombre' => 'Super',
            'apellido_paterno' => 'Admin',
            'apellido_materno' => 'Sistema',
            'email' => 'admin@sice.edu.mx',
            'password' => Hash::make('password123'),
            'sexo' => 'M',
            'rol' => 'super_admin',
            'activo' => true,
            'telefono' => '5550000001',
            'email_verified_at' => now(),
        ]);
        $sa->assignRole('super_admin');

        // Control Escolar
        $ce = User::create([
            'username' => 'control',
            'nombre' => 'María',
            'apellido_paterno' => 'González',
            'apellido_materno' => 'López',
            'email' => 'control@sice.edu.mx',
            'password' => Hash::make('password123'),
            'sexo' => 'F',
            'rol' => 'control_escolar',
            'activo' => true,
            'telefono' => '5550000002',
            'email_verified_at' => now(),
        ]);
        $ce->assignRole('control_escolar');

        // Admin
        $adm = User::create([
            'username' => 'direccion',
            'nombre' => 'Carlos',
            'apellido_paterno' => 'Martínez',
            'apellido_materno' => 'Hernández',
            'email' => 'direccion@sice.edu.mx',
            'password' => Hash::make('password123'),
            'sexo' => 'M',
            'rol' => 'admin',
            'activo' => true,
            'telefono' => '5550000003',
            'email_verified_at' => now(),
        ]);
        $adm->assignRole('admin');
    }
}
