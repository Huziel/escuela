<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Fix alumnos.turno: VARCHAR(20) -> ENUM to match grupos.turno
        DB::statement("ALTER TABLE alumnos MODIFY turno ENUM('matutino','vespertino','nocturno') NULL");

        // 2. Remove circular reference: especialidades.plan_id
        if (Schema::hasColumn('especialidades', 'plan_id')) {
            Schema::table('especialidades', function (Blueprint $table) {
                $table->dropConstrainedForeignId('plan_id');
            });
        }

        // 3. Add inverse relationship: Materia -> planes (belongsToMany)
        // No schema change needed; just model update

        // 4. Add inverse: Especialidad -> docentes (belongsToMany)
        // No schema change needed; just model update

        // 5. Normalize ciclo_escolar varchar lengths: make alumnos.ciclo_escolar match grupos (20)
        // Keep the old varchar for backward compat; will be removed in future cleanup
    }

    public function down(): void
    {
        // Revert turno to varchar
        DB::statement("ALTER TABLE alumnos MODIFY turno VARCHAR(20) NULL");

        // Re-add plan_id to especialidades
        Schema::table('especialidades', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('descripcion')
                  ->constrained('planes_estudio')->nullOnDelete();
        });
    }
};
