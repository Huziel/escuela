<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::truncate();
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
    }
}
