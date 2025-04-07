<?php

namespace App\Console\Commands;
use Laminas\Feed\Reader\Reader;
use App\Models\Mencion;
use Illuminate\Console\Command;

class ProcesarMencionesRSS extends Command
{
    protected $signature = 'app:procesar-menciones-rss';
    protected $description = 'Procesa menciones desde los feeds RSS de Google Alerts';

    public function handle()
    {
        $urls = [
            'https://www.google.es/alerts/feeds/17603787138236543600/5194459168243981893',
            'https://www.google.es/alerts/feeds/17603787138236543600/14698299664815428232'
        ];

        foreach ($urls as $url) {
            $feed = Reader::import($url);

            foreach ($feed as $entry) {
                $titulo = strip_tags(html_entity_decode($entry->getTitle() ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $enlace = $entry->getLink() ?? null;
                $fecha = $entry->getDateModified()?->format('Y-m-d H:i:s') ?? now();
                $descripcionRaw = $entry->getContent() ?? '';
                $descripcionLimpia = strip_tags($descripcionRaw);
                $descripcionLimpia = html_entity_decode($descripcionLimpia);
                $descripcionLimpia = preg_replace('/[^\p{L}\p{N}\s\.\,\-\:\;\/]/u', '', $descripcionLimpia); // Quita símbolos raros
                $descripcion = trim($descripcionLimpia);


                if (!Mencion::where('enlace', $enlace)->exists()) {
                    Mencion::create([
                        'titulo' => $titulo,
                        'enlace' => $enlace,
                        'fuente' => 'Google Alerts',
                        'fecha' => $fecha,
                        'descripcion' => $descripcion,
                    ]);
                }
            }
        }

        $this->info("Menciones procesadas correctamente.");
    }
}