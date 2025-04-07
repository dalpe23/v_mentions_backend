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
            $table->text('titulo');
            $table->string('fuente');
            $table->text('enlace');
            $table->dateTime('fecha');
            $table->text('descripcion');
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
