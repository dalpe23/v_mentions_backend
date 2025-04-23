<?php

namespace App\Console;

use App\Console\Commands\EnviarResumenMenciones;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\ProcesarMencionesRSS;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        ProcesarMencionesRSS::class,
        EnviarResumenMenciones::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('app:procesar-menciones-rss')->everyFiveMinutes();
        $schedule->command('app:enviar-resumen-menciones')->monthlyOn(1, '00:00');

    }    

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
    }
}
