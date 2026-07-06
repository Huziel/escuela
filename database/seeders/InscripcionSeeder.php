<?php
namespace Database\Seeders;

use App\Modules\Inscripciones\Models\Inscripcion;
use App\Modules\Alumnos\Models\Alumno;
use App\Modules\Grupos\Models\Grupo;
use App\Modules\Materias\Models\Materia;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class InscripcionSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_MX');
        $alumnos = Alumno::all();
        $grupos = Grupo::all();

        foreach ($alumnos as $alumno) {
            $grupo = $grupos->where('especialidad_id', $alumno->especialidad_id)
                ->where('semestre', $alumno->semestre)
                ->first() ?? $grupos->random();

            $inscripcion = Inscripcion::create([
                'alumno_id' => $alumno->id,
                'grupo_id' => $grupo->id,
                'tipo' => 'reinscripcion',
                'periodo' => '2024-2025',
                'estatus' => 'aprobada',
                'fecha_solicitud' => now()->subMonths(3),
                'fecha_resolucion' => now()->subMonths(2),
                'documentos_completos' => true,
            ]);

            $materias = Materia::where('especialidad_id', $alumno->especialidad_id)
                ->where('semestre', $alumno->semestre)
                ->take(5)
                ->get();
            if ($materias->isNotEmpty()) {
                $inscripcion->materias()->sync($materias->pluck('id'));
            }
        }
    }
}
