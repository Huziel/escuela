<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Generacion;
use App\Models\CicloEscolar;

class GeneracionCicloSeeder extends Seeder
{
    public function run(): void
    {
        // Generaciones
        $generaciones = [
            ['nombre' => '2025-2029', 'fecha_inicio' => '2025-08-01', 'fecha_termino' => '2029-07-31'],
            ['nombre' => '2026-2030', 'fecha_inicio' => '2026-08-01', 'fecha_termino' => '2030-07-31'],
            ['nombre' => '2027-2031', 'fecha_inicio' => '2027-08-01', 'fecha_termino' => '2031-07-31'],
        ];
        foreach ($generaciones as $g) {
            Generacion::firstOrCreate(['nombre' => $g['nombre']], $g);
        }

        // Ciclos Escolares (for normalization references)
        $ciclos = [];
        for ($year = 2025; $year <= 2027; $year++) {
            for ($sem = 1; $sem <= 2; $sem++) {
                $nombre = "{$year}-{$sem}";
                $inicio = $sem == 1 ? "{$year}-08-01" : "{$year}-01-15";
                $fin = $sem == 1 ? "{$year}-12-31" : "{$year}-07-31";
                CicloEscolar::firstOrCreate(['nombre' => $nombre], [
                    'nombre' => $nombre,
                    'fecha_inicio' => $inicio,
                    'fecha_fin' => $fin,
                    'activo' => true,
                ]);
            }
        }

        echo "Generaciones: " . Generacion::count() . " | Ciclos: " . CicloEscolar::count() . "\n";
    }
}
