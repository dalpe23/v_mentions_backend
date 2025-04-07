<?php

namespace App\Console\Commands;
use willvincent\Feeds\Facades\FeedsFacade as Feeds;
use App\Models\Mencion;
use Illuminate\Console\Command;

class ProcesarMencionesRSS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:procesar-menciones-r-s-s';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $urls = [
            'https://www.google.es/alerts/feeds/17603787138236543600/5194459168243981893',
            'https://www.google.es/alerts/feeds/17603787138236543600/14698299664815428232'
        ];
    
        foreach ($urls as $url) {
            $feed = Feeds::make($url);
    
            foreach ($feed->get_items() as $item) {
                $titulo = $item->get_title();
                $enlace = $item->get_link();
                $fuente = $item->get_author()?->get_name() ?? 'Desconocido';
                $fecha = $item->get_date('Y-m-d H:i:s');
                $descripcion = strip_tags($item->get_description());
    
                if (!Mencion::where('enlace', $enlace)->exists()) {
                    Mencion::create([
                        'titulo' => $titulo,
                        'enlace' => $enlace,
                        'fuente' => $fuente,
                        'fecha' => $fecha,
                        'descripcion' => $descripcion,
                    ]);
                }
            }
        }
    
        $this->info("Menciones procesadas correctamente.");
    }
}
