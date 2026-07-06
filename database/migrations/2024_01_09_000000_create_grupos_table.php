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
        Schema::create('grupos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);
            $table->integer('semestre');
            $table->foreignId('especialidad_id')->constrained('especialidades')->cascadeOnDelete();
            $table->enum('turno', ['matutino', 'vespertino', 'nocturno']);
            $table->integer('capacidad')->default(40);
            $table->foreignId('tutor_id')->nullable()->constrained('docentes')->nullOnDelete();
            $table->string('ciclo_escolar', 20);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};
