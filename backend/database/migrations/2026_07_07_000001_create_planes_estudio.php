<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes_estudio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('especialidad_id')->constrained('especialidades')->cascadeOnDelete();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('plan_materia', function (Blueprint $table) {
            $table->foreignId('plan_id')->constrained('planes_estudio')->cascadeOnDelete();
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete();
            $table->integer('semestre')->nullable();
            $table->integer('horas')->nullable();
            $table->primary(['plan_id', 'materia_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_materia');
        Schema::dropIfExists('planes_estudio');
    }
};
