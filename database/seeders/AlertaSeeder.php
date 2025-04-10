<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Alerta;

class AlertaSeeder extends Seeder
{
    public function run(): void
    {
        Alerta::create([
            'nombre' => 'playas valencia',
        ]);

        Alerta::create([
            'nombre' => 'turismo valencia',
        ]);
    }
}
