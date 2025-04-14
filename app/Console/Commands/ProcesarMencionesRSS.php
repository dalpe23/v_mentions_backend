<?php

namespace App\Console\Commands;

use Laminas\Feed\Reader\Reader;
use App\Models\Mencion;
use Illuminate\Console\Command;

class ProcesarMencionesRSS extends Command
{
    protected $signature = 'app:procesar-menciones-rss';
    protected $description = 'Procesa menciones desde los feeds RSS y asigna alerta_id según cada URL, evitando duplicados incluso en distintos idiomas mediante comparación fuzzy';

    public function handle()
    {
        $urls = config('url');
        $alertaId = 0;

        foreach ($urls as $url) {
            $alertaId++;
            $feed = Reader::import($url);

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

                // Utilizamos la comparación fuzzy para evitar duplicados
                if ($this->isDuplicateTitle($tituloNormalizado)) {
                    continue;
                }

                Mencion::create([
                    'titulo'             => $titulo,
                    'titulo_normalizado' => $tituloNormalizado,
                    'enlace'             => $enlace,
                    'fuente'             => $fuente,
                    'fecha'              => $fecha,
                    'descripcion'        => $descripcion,
                    'alerta_id'          => $alertaId,
                ]);
            }
        }

        $this->info("Menciones procesadas correctamente.");
    }

    /**
     * Normaliza un título para evitar duplicados.
     *
     * Esta función elimina HTML, convierte a minúsculas, quita acentos y signos de puntuación.
     */
    protected function normalizeTitle($title)
    {
        // Decodifica entidades HTML y elimina etiquetas
        $clean = html_entity_decode(strip_tags($title), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Conviértelo a minúsculas
        $clean = mb_strtolower($clean, 'UTF-8');
        // Quitar acentos usando iconv. Si iconv falla, usamos el string original.
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT', $clean);
        if ($normalized === false) {
            $normalized = $clean;
        }
        // Eliminar signos de puntuación y caracteres especiales dejando letras, números y espacios.
        $normalized = preg_replace('/[^a-z0-9\s]/', '', $normalized);
        // Reducir múltiples espacios y recortar.
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        $normalized = trim($normalized);
        // En caso de quedar vacío, usar el título original limpio.
        if ($normalized === '') {
            $normalized = mb_strtolower(trim($clean), 'UTF-8');
        }
        return $normalized;
    }

/**
 * Extrae el dominio real del enlace.
 */
protected function extraerDominio($enlace)
{
    $dominio = 'Desconocido';
    if ($enlace) {
        $parsed = parse_url($enlace);
        if (isset($parsed['host'])) {
            // Remover "www." si está presente:
            $host = preg_replace('/^www\./i', '', $parsed['host']);

            // Si es un enlace de Google y tiene el parámetro 'url', usamos ese enlace real.
            if (in_array(strtolower($parsed['host']), ['google.com', 'www.google.com']) && isset($parsed['query'])) {
                parse_str($parsed['query'], $queryParams);
                if (isset($queryParams['url'])) {
                    $urlReal = $queryParams['url'];
                    $parsedReal = parse_url($urlReal);
                    if (isset($parsedReal['host'])) {
                        // Remover "www." si aparece
                        $dominio = preg_replace('/^www\./i', '', $parsedReal['host']);
                    } else {
                        $dominio = $urlReal; // fallback
                    }
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
     * Comprueba si existe una mención con un título similar usando comparación fuzzy.
     *
     * @param string $tituloNormalizado
     * @return bool
     */
    protected function isDuplicateTitle($tituloNormalizado)
    {
        // Obtiene todos los títulos normalizados existentes
        $existingTitles = Mencion::pluck('titulo_normalizado')->toArray();
        foreach ($existingTitles as $existing) {
            // Usa similar_text para calcular el porcentaje de similitud
            similar_text($existing, $tituloNormalizado, $percent);
            // Si la similitud es mayor que el 90% (ajusta el umbral si es necesario), considera que es duplicado
            if ($percent >= 90) {
                return true;
            }
        }
        return false;
    }
}
