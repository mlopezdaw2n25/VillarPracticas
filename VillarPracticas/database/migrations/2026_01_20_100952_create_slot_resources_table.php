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
Schema::create('slot_resources', function (Blueprint $table) {
    $table->id();
    $table->foreignId('time_slot_id')->constrained('time_slots')->cascadeOnDelete();
    $table->foreignId('resource_id')->constrained('resources')->cascadeOnDelete();
    $table->unsignedInteger('available_units')->default(1);
    $table->timestamps();

    $table->unique(['time_slot_id', 'resource_id']);
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slot_resources');
    }
};
