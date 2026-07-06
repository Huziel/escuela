<?php
namespace Database\Seeders;

use App\Modules\Materias\Models\Materia;
use App\Modules\Especialidades\Models\Especialidad;
use Illuminate\Database\Seeder;

class MateriaSeeder extends Seeder
{
    public function run(): void
    {
        $especialidades = Especialidad::all();
        if ($especialidades->isEmpty()) return;

        $materiasPorSemestre = [
            1 => ['Matemáticas I','Inglés I','Química I','Lectura y Expresión','Tecnologías de la Información'],
            2 => ['Matemáticas II','Inglés II','Química II','Ética y Valores','Historia de México'],
            3 => ['Matemáticas III','Inglés III','Biología','Física I','Módulo Profesional I'],
            4 => ['Matemáticas IV','Inglés IV','Física II','Ecología','Módulo Profesional II'],
            5 => ['Matemáticas V','Inglés V','Cálculo','Módulo Profesional III','Submódulo I'],
            6 => ['Matemáticas VI','Filosofía','Probabilidad y Estadística','Módulo Profesional IV','Submódulo II'],
        ];

        $i = 1;
        foreach ($especialidades as $esp) {
            foreach ($materiasPorSemestre as $semestre => $materias) {
                foreach ($materias as $nombre) {
                    Materia::create([
                        'clave' => $esp->clave.'-'.str_pad($i, 3, '0', STR_PAD_LEFT),
                        'nombre' => $nombre,
                        'creditos' => rand(3, 10),
                        'horas_teoricas' => rand(2, 4),
                        'horas_practicas' => rand(1, 3),
                        'semestre' => $semestre,
                        'especialidad_id' => $esp->id,
                        'activo' => true,
                    ]);
                    $i++;
                }
            }
        }
    }
}
