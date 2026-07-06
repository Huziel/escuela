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
        Schema::create('alumnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('matricula', 20)->unique();
            $table->string('curp', 18)->nullable()->unique();
            $table->foreignId('especialidad_id')->constrained('especialidades')->cascadeOnDelete();
            $table->integer('semestre');
            $table->foreignId('grupo_id')->nullable();
            $table->foreignId('tutor_id')->nullable();
            $table->enum('estatus', ['activo', 'inactivo', 'egresado', 'baja', 'bloqueado'])->default('activo');
            $table->date('fecha_ingreso');
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
        Schema::dropIfExists('alumnos');
    }
};
