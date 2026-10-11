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
        Schema::create('regularizaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete();
            $table->foreignId('calificacion_id')->nullable()->constrained('calificaciones')->nullOnDelete();
            $table->string('periodo', 20);
            $table->date('fecha_examen');
            $table->decimal('calificacion', 5, 2);
            $table->enum('estatus', ['pendiente', 'aprobada', 'reprobada'])->default('pendiente');
            $table->decimal('pago', 10, 2)->default(0);
            $table->boolean('pagado')->default(false);
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
        Schema::dropIfExists('regularizaciones');
    }
};
