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
        Schema::table('menciones', function (Blueprint $table) {
            $table->string('titulo_alerta')->nullable()->after('alerta_id'); // Añadir el campo `titulo_alerta`
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menciones', function (Blueprint $table) {
            $table->dropColumn('titulo_alerta'); // Eliminar el campo `titulo_alerta` en caso de rollback
        });
    }
};
