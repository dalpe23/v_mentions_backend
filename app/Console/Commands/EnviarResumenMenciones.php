<?php

namespace App\Console\Commands;

use App\Models\Mencion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResumenMencionesMail;

class EnviarResumenMenciones extends Command
{
    protected $signature = 'app:enviar-resumen-menciones';
    protected $description = 'Envía un resumen por correo con las menciones de sentimiento neutro o negativo del último mes.';

    public function handle()
    {
        $usuarios = \App\Models\User::with('alertEmails')->get();
    
        foreach ($usuarios as $usuario) {
            $this->info("Procesando usuario: {$usuario->id}");

            // Mostrar menciones asociadas a alertas del usuario en el último mes, solo neutro o negativo
            $menciones = \App\Models\Mencion::whereHas('alerta', function ($q) use ($usuario) {
                $q->where('user_id', $usuario->id);
            })
            ->whereIn('sentimiento', ['neutro', 'negativo'])
            ->where('created_at', '>=', now()->subMonth())
            ->orderByDesc('created_at')
            ->get();

            $this->info("Menciones encontradas: " . $menciones->count());

            foreach ($menciones as $mencion) {
                $this->line("- {$mencion->titulo} ({$mencion->created_at}) [{$mencion->sentimiento}]");
            }

            if ($menciones->isNotEmpty()) {
                if ($usuario->alertEmails->isEmpty()) {
                    $this->warn("⚠️ El usuario {$usuario->id} no tiene emails de alerta configurados.");
                    continue;
                }

                foreach ($usuario->alertEmails as $alertEmail) {
                    Mail::to($alertEmail->email)->send(new ResumenMencionesMail($menciones));
                    $this->info("📧 Correo enviado a {$alertEmail->email} con " . $menciones->count() . " menciones.");
                }
            } else {
                $this->info("No hay menciones para usuario {$usuario->id} con sentimiento neutro o negativo.");
            }
        }
    }
}
