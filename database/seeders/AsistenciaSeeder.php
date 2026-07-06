<?php
namespace Database\Seeders;

use App\Modules\Asistencia\Models\Asistencia;
use App\Modules\Alumnos\Models\Alumno;
use App\Modules\Grupos\Models\Grupo;
use App\Modules\Materias\Models\Materia;
use App\Modules\Docentes\Models\Docente;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AsistenciaSeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = Alumno::take(25)->get();
        $docentes = Docente::all();

        foreach ($alumnos as $alumno) {
            $materias = Materia::where('especialidad_id', $alumno->especialidad_id)
                ->where('semestre', $alumno->semestre)
                ->take(3)
                ->get();

            $grupo = Grupo::where('id', $alumno->grupo_id)->first();

            foreach ($materias as $materia) {
                for ($d = 0; $d < 4; $d++) {
                    $fecha = Carbon::now()->subDays(rand(1, 30))->format('Y-m-d');
                    $estatus = rand(1, 10) > 1 ? 'asistencia' : (rand(0, 1) ? 'falta' : 'retardo');

                    Asistencia::create([
                        'alumno_id' => $alumno->id,
                        'grupo_id' => $grupo?->id ?? 1,
                        'materia_id' => $materia->id,
                        'docente_id' => $docentes->random()->id,
                        'fecha' => $fecha,
                        'estatus' => $estatus,
                        'justificacion' => $estatus === 'falta' ? 'Sin justificación' : null,
                        'registrado_por' => 1,
                    ]);
                }
            }
        }
    }
}
