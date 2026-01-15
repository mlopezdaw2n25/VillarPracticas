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
        Schema::create('usuaris', function (Blueprint $table) {
            $table->id();
            $table->string('nom',10);
            $table->string('email',50);
            $table->string('contrasenya',50);
            $table->string('correo_notificaciones',50);
            $table->string('rol',10);
            $table->string('activo',10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuaris');
    }
};
