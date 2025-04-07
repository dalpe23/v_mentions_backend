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
        Schema::create('menciones', function (Blueprint $table) {
            $table->id();
            $table->text('texto');
            $table->string('fuente');
            $table->dateTime('fecha');
            $table->enum('sentimiento', ['positivo', 'negativo', 'neutro'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menciones');
    }
};
