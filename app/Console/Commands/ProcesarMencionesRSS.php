<?php

namespace App\Console\Commands;

use Laminas\Feed\Reader\Reader;
use App\Models\Mencion;
use Illuminate\Console\Command;
use App\Services\OpenAIService;

class ProcesarMencionesRSS extends Command
{
    protected $signature = 'app:procesar-menciones-rss';
    protected $description = 'Procesa menciones desde los feeds RSS, evitando duplicados y completando el análisis (sentimiento y temática) en aquellas que aun no tienen datos.';

    public function handle()
    {
        $openAI = new OpenAIService();
        $feeds = config('url');  // Cargar el archivo de configuración completo
        // Itera sobre cada alerta y su URL
        foreach ($feeds as $alerta => $data) {
            $alertaId = $data['alerta_id'];  // Obtener el alerta_id directamente desde el archivo de configuración
            $url = $data['url'];  // Obtener la URL desde el archivo de configuración

            $feed = Reader::import($url);  // Cargar el feed RSS desde la URL

            foreach ($feed as $entry) {
                $titulo = strip_tags(html_entity_decode($entry->getTitle() ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $tituloNormalizado = $this->normalizeTitle($titulo);
                $enlace = $entry->getLink() ?? null;
                $fecha = $entry->getDateModified()?->format('Y-m-d H:i:s') ?? now();

                $descripcionRaw = $entry->getContent() ?? '';
                $descripcionLimpia = strip_tags(html_entity_decode($descripcionRaw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $descripcionLimpia = preg_replace('/[^\p{L}\p{N}\s\.\,\-\:\;\/]/u', '', $descripcionLimpia);
                $descripcion = trim($descripcionLimpia);

                $fuente = $this->extraerDominio($enlace);

                // Evita duplicados (comparación fuzzy en título normalizado)
                if ($this->isDuplicateTitle($tituloNormalizado)) {
                    continue;
                }

                // Creación de nueva mención (sentimiento y temática quedan nulos)
                $mencion = Mencion::create([
                    'titulo'             => $titulo,
                    'titulo_normalizado' => $tituloNormalizado,
                    'enlace'             => $enlace,
                    'fuente'             => $fuente,
                    'fecha'              => $fecha,
                    'descripcion'        => $descripcion,
                    'alerta_id'          => $alertaId,  // Asignamos el alerta_id directamente desde la configuración
                ]);

                // Si la mención recién creada aún no tiene análisis, se procesa:
                if (is_null($mencion->sentimiento) || is_null($mencion->tematica)) {
                    $this->analizarYActualizarMencion($mencion, $openAI);
                }
            }
        }

        // ----- Paso 2: Actualizar menciones existentes que no tienen análisis completado -----
        $mencionesIncompletas = Mencion::whereNull('sentimiento')
            ->orWhereNull('tematica')
            ->get();

        if ($mencionesIncompletas->isNotEmpty()) {
            foreach ($mencionesIncompletas as $mencion) {
                $this->analizarYActualizarMencion($mencion, $openAI);
            }
        }

        $this->info("Menciones procesadas y completadas correctamente.");
    }

    /**
     * Analiza una mención usando el servicio OpenAI y actualiza sus campos de sentimiento y temática.
     *
     * @param \App\Models\Mencion $mencion
     * @param \App\Services\OpenAIService $openAI
     */
    protected function analizarYActualizarMencion($mencion, OpenAIService $openAI)
    {
        // Combina título y descripción para un análisis más completo
        $textoAnalizar = "{$mencion->titulo}. {$mencion->descripcion}";
        $analysis = $openAI->analizarSentimientoYTematica($textoAnalizar);

        if ($analysis) {
            $sentimientoGPT = strtolower($analysis['sentimiento'] ?? 'neutro');

            $sentimiento = match ($sentimientoGPT) {
                'positivo', 'positive' => 'positivo',
                'negativo', 'negative' => 'negativo',
                'neutro', 'neutral', null => 'neutro',
                default => 'neutro'
            };

            $tematicas = isset($analysis['tematicas']) && is_array($analysis['tematicas'])
                ? implode(', ', $analysis['tematicas'])
                : '';

            $mencion->update([
                'sentimiento' => $sentimiento,
                'tematica'    => $tematicas,
            ]);

            $this->line("✅ Mención ID {$mencion->id} actualizada: sentimiento={$sentimiento}, tematica={$tematicas}");
        } else {
            $this->warn("❌ No se pudo analizar la mención ID {$mencion->id}");
        }
    }

    /**
     * Normaliza un título: elimina HTML, lo convierte a minúsculas, quita acentos y signos de puntuación.
     *
     * @param string $title
     * @return string
     */
    protected function normalizeTitle($title)
    {
        $clean = html_entity_decode(strip_tags($title), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $clean = mb_strtolower($clean, 'UTF-8');
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT', $clean);
        if ($normalized === false) {
            $normalized = $clean;
        }
        $normalized = preg_replace('/[^a-z0-9\s]/', '', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        return trim($normalized ?: $clean);
    }

    /**
     * Extrae el dominio real del enlace, eliminando el prefijo "www." si existe.
     *
     * @param string|null $enlace
     * @return string
     */
    protected function extraerDominio($enlace)
    {
        $dominio = 'Desconocido';
        if ($enlace) {
            $parsed = parse_url($enlace);
            if (isset($parsed['host'])) {
                $host = preg_replace('/^www\./i', '', $parsed['host']);
                if (in_array(strtolower($parsed['host']), ['google.com', 'www.google.com']) && isset($parsed['query'])) {
                    parse_str($parsed['query'], $queryParams);
                    if (isset($queryParams['url'])) {
                        $urlReal = $queryParams['url'];
                        $parsedReal = parse_url($urlReal);
                        $dominio = isset($parsedReal['host']) ? preg_replace('/^www\./i', '', $parsedReal['host']) : $urlReal;
                    } else {
                        $dominio = $host;
                    }
                } else {
                    $dominio = $host;
                }
            }
        }
        return $dominio;
    }

    /**
     * Comprueba si existe una mención similar usando comparación fuzzy en el título normalizado.
     *
     * @param string $tituloNormalizado
     * @return bool
     */
    protected function isDuplicateTitle($tituloNormalizado)
    {
        $existingTitles = Mencion::pluck('titulo_normalizado')->toArray();
        foreach ($existingTitles as $existing) {
            similar_text($existing, $tituloNormalizado, $percent);
            if ($percent >= 90) {
                return true;
            }
        }
        return false;
    }
}
