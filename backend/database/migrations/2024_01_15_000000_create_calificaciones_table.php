<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete();
            $table->foreignId('grupo_id')->constrained('grupos')->cascadeOnDelete();
            $table->foreignId('docente_id')->nullable()->constrained('docentes')->nullOnDelete();
            $table->string('periodo', 20);
            $table->decimal('parcial1', 5, 2)->nullable();
            $table->decimal('parcial2', 5, 2)->nullable();
            $table->decimal('parcial3', 5, 2)->nullable();
            $table->decimal('ordinario', 5, 2)->nullable();
            $table->decimal('extraordinario', 5, 2)->nullable();
            $table->decimal('promedio_final', 5, 2)->nullable();
            $table->boolean('redondeo')->default(true);
            $table->enum('estatus', ['cursando', 'aprobada', 'reprobada', 'regularizacion'])->default('cursando');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
    }
};
