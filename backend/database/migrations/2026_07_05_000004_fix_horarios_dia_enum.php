<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE horarios MODIFY COLUMN dia VARCHAR(20) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE horarios MODIFY COLUMN dia ENUM('lunes','martes','miercoles','jueves','viernes','sabado') NOT NULL");
    }
};
