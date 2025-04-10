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
        $urls = config('url');

        $alertaId = 0;

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
