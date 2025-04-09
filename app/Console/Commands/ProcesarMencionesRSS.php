<?php

namespace App\Console\Commands;

use Laminas\Feed\Reader\Reader;
use App\Models\Mencion;
use Illuminate\Console\Command;

class ProcesarMencionesRSS extends Command
{
    protected $signature = 'app:procesar-menciones-rss';
    protected $description = 'Procesa menciones desde los feeds RSS de Google Alerts y asigna alerta_id según cada URL';

    public function handle()
    {
        // config('url') debe devolver un array con, por ejemplo, 2 URLs.
        $urls = config('url');

        // Inicializamos el contador de alerta_id.
        $alertaId = 0;

        // Para cada URL se incrementa alertaId; de esta forma, las menciones del primer enlace tendrán alerta_id 1,
        // las del segundo tendrán alerta_id 2, y así sucesivamente.
        foreach ($urls as $url) {
            $alertaId++;
            $feed = Reader::import($url);

            foreach ($feed as $entry) {
                $titulo = strip_tags(html_entity_decode($entry->getTitle() ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $enlace = $entry->getLink() ?? null;
                $fecha = $entry->getDateModified()?->format('Y-m-d H:i:s') ?? now();

                $descripcionRaw = $entry->getContent() ?? '';
                $descripcionLimpia = strip_tags(html_entity_decode($descripcionRaw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $descripcionLimpia = preg_replace('/[^\p{L}\p{N}\s\.\,\-\:\;\/]/u', '', $descripcionLimpia);
                $descripcion = trim($descripcionLimpia);

                // Solo se crea la mención si no existe ya una con ese enlace
                if (!Mencion::where('enlace', $enlace)->exists()) {
                    Mencion::create([
                        'titulo'      => $titulo,
                        'enlace'      => $enlace,
                        'fuente'      => 'Google Alerts',
                        'fecha'       => $fecha,
                        'descripcion' => $descripcion,
                        'alerta_id'   => $alertaId,
                    ]);
                }
            }
        }

        $this->info("Menciones procesadas correctamente.");
    }
}
