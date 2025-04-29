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
            'url' => 'https://www.google.es/alerts/feeds/17603787138236543600/5194459168243981893',
        ]);

        Alerta::create([
            'nombre' => 'turismo valencia',
            'user_id' => 2,
            'url' => 'https://www.google.es/alerts/feeds/17603787138236543600/14698299664815428232',
        ]);
    }
}
