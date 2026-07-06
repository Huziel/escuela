<?php
namespace Database\Seeders;

use App\Models\Configuracion;
use App\Models\CicloEscolar;
use Illuminate\Database\Seeder;

class ConfiguracionSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            ['clave' => 'institucion_nombre', 'valor' => 'CETIS No. 1', 'tipo' => 'string', 'descripcion' => 'Nombre de la institución'],
            ['clave' => 'institucion_clave', 'valor' => '09DCT0001Z', 'tipo' => 'string', 'descripcion' => 'Clave de la institución'],
            ['clave' => 'institucion_direccion', 'valor' => 'Av. Principal #123, Ciudad de México', 'tipo' => 'string', 'descripcion' => 'Dirección'],
            ['clave' => 'institucion_telefono', 'valor' => '5551234567', 'tipo' => 'string', 'descripcion' => 'Teléfono'],
            ['clave' => 'institucion_email', 'valor' => 'contacto@sice.edu.mx', 'tipo' => 'string', 'descripcion' => 'Correo electrónico'],
            ['clave' => 'calificacion_min_aprobatoria', 'valor' => '6', 'tipo' => 'integer', 'descripcion' => 'Calificación mínima aprobatoria'],
            ['clave' => 'calificacion_maxima', 'valor' => '10', 'tipo' => 'integer', 'descripcion' => 'Calificación máxima'],
            ['clave' => 'faltas_maximas', 'valor' => '20', 'tipo' => 'integer', 'descripcion' => 'Faltas máximas por parcial'],
            ['clave' => 'periodo_actual', 'valor' => '2024-2025', 'tipo' => 'string', 'descripcion' => 'Período actual'],
            ['clave' => 'redondeo_calificaciones', 'valor' => 'true', 'tipo' => 'boolean', 'descripcion' => 'Redondear calificaciones'],
        ];

        foreach ($configs as $c) Configuracion::create($c);

        CicloEscolar::create([
            'nombre' => '2024-2025',
            'fecha_inicio' => '2024-08-26',
            'fecha_fin' => '2025-07-11',
            'activo' => true,
        ]);
    }
}
