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
        Schema::create('docentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('numero_empleado', 20)->unique();
            $table->string('curp', 18)->nullable()->unique();
            $table->string('especialidad', 200)->nullable();
            $table->string('grado_academico', 100)->nullable();
            $table->string('rfc', 13)->nullable();
            $table->enum('estatus', ['activo', 'inactivo', 'baja'])->default('activo');
            $table->date('fecha_ingreso');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('docentes');
    }
};
