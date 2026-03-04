<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {

            $table->string('status')
                ->default('activa')
                ->after('time_slot_id');

            $table->boolean('return_defectuoso')
                ->default(false)
                ->after('status');

            $table->text('return_comentario')
                ->nullable()
                ->after('return_defectuoso');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'return_defectuoso',
                'return_comentario'
            ]);
        });
    }
};