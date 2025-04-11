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
            'user_id' => 1,
        ]);

        Alerta::create([
            'nombre' => 'turismo valencia',
            'user_id' => 2,
        ]);
    }
}
