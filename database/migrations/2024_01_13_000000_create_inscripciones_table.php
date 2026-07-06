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
        Schema::create('inscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->foreignId('grupo_id')->constrained('grupos')->cascadeOnDelete();
            $table->enum('tipo', ['nuevo_ingreso', 'reinscripcion']);
            $table->string('periodo', 20);
            $table->enum('estatus', ['pendiente', 'aprobada', 'rechazada', 'cancelada'])->default('pendiente');
            $table->date('fecha_solicitud');
            $table->date('fecha_resolucion')->nullable();
            $table->boolean('documentos_completos')->default(false);
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
        Schema::dropIfExists('inscripciones');
    }
};
