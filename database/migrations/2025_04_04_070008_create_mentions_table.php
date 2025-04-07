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
        Schema::create('mentions', function (Blueprint $table) {
            $table->id();
            $table->string('url')->unique();
            $table->text('texto');
            $table->string('fuente');
            $table->datetime('fecha');
            $table->string('sentimiento')->nullable();
            $table->string('tema')->nullable();
            $table->boolean('alerta_negativa')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentions');
    }
};
