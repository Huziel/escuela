<?php
namespace Database\Seeders;

use App\Modules\Grupos\Models\Grupo;
use App\Modules\Especialidades\Models\Especialidad;
use App\Modules\Docentes\Models\Docente;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class GrupoSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_MX');
        $especialidades = Especialidad::all();
        $docentes = Docente::all();
        $turnos = ['matutino','vespertino','nocturno'];
        $letras = ['A','B','C','D','E'];

        foreach ($especialidades as $esp) {
            for ($sem = 1; $sem <= 2; $sem++) {
                $cantidadGrupos = $esp->id <= 2 ? 2 : 1;
                for ($g = 0; $g < $cantidadGrupos; $g++) {
                    Grupo::create([
                        'nombre' => $sem.'°'.$letras[$g],
                        'semestre' => $sem,
                        'especialidad_id' => $esp->id,
                        'turno' => $faker->randomElement($turnos),
                        'capacidad' => 40,
                        'tutor_id' => $docentes->random()->id,
                        'ciclo_escolar' => '2024-2025',
                        'activo' => true,
                    ]);
                }
            }
        }
    }
}
