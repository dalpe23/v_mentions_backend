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
        Schema::create('mencion_tema', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mencion_id')->constrained('menciones')->onDelete('cascade');
            $table->foreignId('tema_id')->constrained('temas')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mencion_tema');
    }
};
