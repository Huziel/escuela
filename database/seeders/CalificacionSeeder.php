<?php
namespace Database\Seeders;

use App\Modules\Calificaciones\Models\Calificacion;
use App\Modules\Alumnos\Models\Alumno;
use App\Modules\Materias\Models\Materia;
use App\Modules\Grupos\Models\Grupo;
use App\Modules\Docentes\Models\Docente;
use Illuminate\Database\Seeder;

class CalificacionSeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = Alumno::all();
        $docentes = Docente::all();

        foreach ($alumnos as $alumno) {
            $materias = Materia::where('especialidad_id', $alumno->especialidad_id)
                ->where('semestre', $alumno->semestre)
                ->take(4)
                ->get();

            $grupo = Grupo::where('id', $alumno->grupo_id)->first();

            foreach ($materias as $materia) {
                $p1 = rand(5, 10) + (rand(0, 10) / 10);
                $p2 = rand(5, 10) + (rand(0, 10) / 10);
                $p3 = rand(5, 10) + (rand(0, 10) / 10);
                $prom = round(($p1 + $p2 + $p3) / 3, 2);
                $estatus = $prom >= 6 ? 'aprobada' : 'reprobada';

                Calificacion::create([
                    'alumno_id' => $alumno->id,
                    'materia_id' => $materia->id,
                    'grupo_id' => $grupo?->id ?? 1,
                    'docente_id' => $docentes->random()->id,
                    'periodo' => '2024-2025',
                    'parcial1' => $p1,
                    'parcial2' => $p2,
                    'parcial3' => $p3,
                    'ordinario' => null,
                    'extraordinario' => null,
                    'promedio_final' => $prom,
                    'redondeo' => true,
                    'estatus' => $estatus,
                ]);
            }
        }
    }
}
