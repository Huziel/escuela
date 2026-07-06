<?php
namespace Database\Seeders;

use App\Modules\Especialidades\Models\Especialidad;
use Illuminate\Database\Seeder;

class EspecialidadSeeder extends Seeder
{
    public function run(): void
    {
        $especialidades = [
            ['clave' => 'PROG', 'nombre' => 'Programación', 'descripcion' => 'Técnico en Programación de Software', 'activo' => true],
            ['clave' => 'CONT', 'nombre' => 'Contabilidad', 'descripcion' => 'Técnico en Contabilidad y Finanzas', 'activo' => true],
            ['clave' => 'ELEC', 'nombre' => 'Electrónica', 'descripcion' => 'Técnico en Electrónica Industrial', 'activo' => true],
            ['clave' => 'MECA', 'nombre' => 'Mecatrónica', 'descripcion' => 'Técnico en Mecatrónica', 'activo' => true],
            ['clave' => 'ADMI', 'nombre' => 'Administración', 'descripcion' => 'Técnico en Administración de Empresas', 'activo' => true],
            ['clave' => 'DISE', 'nombre' => 'Diseño Gráfico', 'descripcion' => 'Técnico en Diseño Gráfico Digital', 'activo' => true],
            ['clave' => 'LABO', 'nombre' => 'Laboratorio Clínico', 'descripcion' => 'Técnico en Laboratorio Clínico', 'activo' => true],
            ['clave' => 'ALIM', 'nombre' => 'Alimentos y Bebidas', 'descripcion' => 'Técnico en Alimentos y Bebidas', 'activo' => true],
        ];
        foreach ($especialidades as $e) Especialidad::create($e);
    }
}
