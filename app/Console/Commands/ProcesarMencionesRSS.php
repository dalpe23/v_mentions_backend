<?php

namespace App\Console\Commands;

use Laminas\Feed\Reader\Reader;
use App\Models\Mencion;
use Illuminate\Console\Command;
use App\Services\OpenAIService;
use App\Models\Alerta;
use Carbon\Carbon;

class ProcesarMencionesRSS extends Command
{
    protected $signature = 'app:procesar-menciones-rss';
    protected $description = 'Procesa menciones desde los feeds RSS, evitando duplicados y completando el análisis (sentimiento y temática) en aquellas que aun no tienen datos.';

    public function handle()
    {
        $openAI  = new OpenAIService();
        $alertas = Alerta::where('resuelta', false)->get();

        foreach ($alertas as $alerta) {
            if (! $alerta->url) {
                continue;
            }

            try {
                $feed = Reader::import($alerta->url);
            } catch (\Exception $e) {
                $this->error("Error al importar RSS alerta ID {$alerta->id}: {$e->getMessage()}");
                continue;
            }

            foreach ($feed as $entry) {
                $fechaRaw = $entry->getDateModified()?->format('Y-m-d H:i:s');
                $fecha     = $fechaRaw
                    ? Carbon::parse($fechaRaw)
                    : Carbon::now();

                if ($fecha->lt(Carbon::now()->subWeek())) {
                    continue;
                }
                $fechaStr = $fecha->format('Y-m-d H:i:s');

                $titulo            = strip_tags(html_entity_decode($entry->getTitle() ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $tituloNormalizado = $this->normalizeTitle($titulo);
                $enlace            = $entry->getLink() ?? '';
                $fecha             = $entry->getDateModified()?->format('Y-m-d H:i:s') ?? now();

                $m = Mencion::where('titulo_normalizado', $tituloNormalizado)->first();
                if ($m) {
                    if ($m->sentimiento && $m->tematica) {
                        continue;
                    }
                    $this->analizarYActualizarMencion($m, $openAI);
                    continue;
                }

                $descripcion = $openAI->generarDescripcionDesdeTitulo($titulo) ?? 'Sin descripción disponible.';

                $xml       = @simplexml_load_string($entry->saveXml());
                $fuenteUrl = $xml && isset($xml->source) ? (string) $xml->source['url'] : $enlace;

                $pais = $this->extraerPaisDeUrl($fuenteUrl)
                    ?? $this->inferirPaisPorIA($fuenteUrl, $titulo);

                $fuente = $fuenteUrl . ($pais ? " - {$pais}" : '');

                $m = Mencion::create([
                    'titulo'             => $titulo,
                    'titulo_normalizado' => $tituloNormalizado,
                    'enlace'             => $enlace,
                    'fuente'             => $fuente,
                    'fecha'              => $fecha,
                    'descripcion'        => $descripcion,
                    'alerta_id'          => $alerta->id,
                ]);

                $this->analizarYActualizarMencion($m, $openAI);
            }
        }

        $this->info("Menciones procesadas y completadas correctamente.");
    }

    protected function inferirPaisPorIA(string $url, string $titulo): ?string
    {
        $ctx = "URL de la fuente: {$url}. Título: {$titulo}.";
        $inf = app(OpenAIService::class)->inferirPaisDesdeTexto($ctx);
        return $inf && strtolower($inf) !== 'desconocido' ? trim($inf) : null;
    }

    protected function analizarYActualizarMencion($mencion, OpenAIService $openAI)
    {
        $texto = "{$mencion->titulo}. {$mencion->descripcion}";
        try {
            $analysis = $openAI->analizarSentimientoYTematica($texto);
        } catch (\Exception $e) {
            $this->error("Error en análisis de la mención ID {$mencion->id}: {$e->getMessage()}");
            return;
        }

        if ($analysis) {
            $sent = strtolower($analysis['sentimiento'] ?? 'neutro');
            $sent = match ($sent) {
                'positivo', 'positive' => 'positivo',
                'negativo', 'negative' => 'negativo',
                default               => 'neutro',
            };
            $temas = is_array($analysis['tematicas'])
                ? implode(', ', $analysis['tematicas'])
                : '';

            $mencion->update([
                'sentimiento' => $sent,
                'tematica'    => $temas,
            ]);

            $this->line("Mención ID {$mencion->id} actualizada: sentimiento={$sent}, tematica={$temas}");
        } else {
            $this->error("OpenAI no devolvió análisis para la mención ID {$mencion->id}");
        }
    }


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

    protected function extraerPaisDeUrl(string $url): ?string
    {
        if (! $url) return null;
        $host = parse_url($url, PHP_URL_HOST);
        $host = preg_replace('/^www\./i', '', $host);
        if (! preg_match('/\.([a-z]{2,3})$/i', $host, $m)) {
            return null;
        }
        $tld = strtolower($m[1]);
        $map = [
            'es' => 'España',
            'fr' => 'Francia',
            'it' => 'Italia',
            'pt' => 'Portugal',
            'de' => 'Alemania',
            'uk' => 'Reino Unido',
            'ie' => 'Irlanda',
            'nl' => 'Países Bajos',
            'be' => 'Bélgica',
            'ch' => 'Suiza',
            'at' => 'Austria',
            'se' => 'Suecia',
            'no' => 'Noruega',
            'fi' => 'Finlandia',
            'dk' => 'Dinamarca',
            'pl' => 'Polonia',
            'cz' => 'República Checa',
            'gr' => 'Grecia',
            'hu' => 'Hungría',
            'ro' => 'Rumanía',
            'bg' => 'Bulgaria',
            'sk' => 'Eslovaquia',
            'si' => 'Eslovenia',
            'hr' => 'Croacia',
            'rs' => 'Serbia',
            'ua' => 'Ucrania',
            'lt' => 'Lituania',
            'lv' => 'Letonia',
            'ee' => 'Estonia',
            'lu' => 'Luxemburgo',
            'md' => 'Moldavia',
            'al' => 'Albania',
            'by' => 'Bielorrusia',
            'ba' => 'Bosnia y Herzegovina',
            'mk' => 'Macedonia del Norte',
            'mt' => 'Malta',
            'mc' => 'Mónaco',
            'li' => 'Liechtenstein',
            'sm' => 'San Marino',
            'va' => 'Vaticano',
            'gi' => 'Gibraltar',
            'is' => 'Islandia',
            'tr' => 'Turquía',
            'us' => 'Estados Unidos',
            'ca' => 'Canadá',
            'mx' => 'México',
            'ar' => 'Argentina',
            'br' => 'Brasil',
            'cl' => 'Chile',
            'co' => 'Colombia',
            'pe' => 'Perú',
            've' => 'Venezuela',
            'uy' => 'Uruguay',
            'ec' => 'Ecuador',
            'bo' => 'Bolivia',
            'py' => 'Paraguay',
            'cr' => 'Costa Rica',
            'do' => 'República Dominicana',
            'cu' => 'Cuba',
            'gt' => 'Guatemala',
            'hn' => 'Honduras',
            'ni' => 'Nicaragua',
            'sv' => 'El Salvador',
            'pa' => 'Panamá',
            'pr' => 'Puerto Rico',
            'jm' => 'Jamaica',
            'tt' => 'Trinidad y Tobago',
            'bs' => 'Bahamas',
            'bb' => 'Barbados',
            'ag' => 'Antigua y Barbuda',
            'dm' => 'Dominica',
            'gd' => 'Granada',
            'kn' => 'San Cristóbal y Nieves',
            'lc' => 'Santa Lucía',
            'vc' => 'San Vicente y las Granadinas',
            'sr' => 'Surinam',
            'gy' => 'Guyana',
            'bz' => 'Belice',
            'za' => 'Sudáfrica',
            'ng' => 'Nigeria',
            'eg' => 'Egipto',
            'ma' => 'Marruecos',
            'dz' => 'Argelia',
            'tn' => 'Túnez',
            'ke' => 'Kenia',
            'gh' => 'Ghana',
            'et' => 'Etiopía',
            'sn' => 'Senegal',
            'ug' => 'Uganda',
            'zm' => 'Zambia',
            'zw' => 'Zimbabue',
            'ao' => 'Angola',
            'cm' => 'Camerún',
            'ci' => 'Costa de Marfil',
            'mg' => 'Madagascar',
            'mw' => 'Malaui',
            'mz' => 'Mozambique',
            'na' => 'Namibia',
            'bw' => 'Botsuana',
            'bf' => 'Burkina Faso',
            'cd' => 'República Democrática del Congo',
            'cg' => 'República del Congo',
            'ga' => 'Gabón',
            'gm' => 'Gambia',
            'gn' => 'Guinea',
            'gw' => 'Guinea-Bisáu',
            'lr' => 'Liberia',
            'ml' => 'Malí',
            'mr' => 'Mauritania',
            'ne' => 'Níger',
            'rw' => 'Ruanda',
            'sc' => 'Seychelles',
            'sl' => 'Sierra Leona',
            'so' => 'Somalia',
            'sd' => 'Sudán',
            'sz' => 'Suazilandia',
            'tg' => 'Togo',
            'td' => 'Chad',
            'cf' => 'República Centroafricana',
            'dj' => 'Yibuti',
            'er' => 'Eritrea',
            'ls' => 'Lesoto',
            'st' => 'Santo Tomé y Príncipe',
            'cn' => 'China',
            'jp' => 'Japón',
            'in' => 'India',
            'kr' => 'Corea del Sur',
            'sg' => 'Singapur',
            'th' => 'Tailandia',
            'id' => 'Indonesia',
            'my' => 'Malasia',
            'ph' => 'Filipinas',
            'hk' => 'Hong Kong',
            'tw' => 'Taiwán',
            'il' => 'Israel',
            'sa' => 'Arabia Saudita',
            'ae' => 'Emiratos Árabes Unidos',
            'qa' => 'Qatar',
            'pk' => 'Pakistán',
            'ir' => 'Irán',
            'iq' => 'Irak',
            'jo' => 'Jordania',
            'kw' => 'Kuwait',
            'lb' => 'Líbano',
            'om' => 'Omán',
            'sy' => 'Siria',
            'ye' => 'Yemen',
            'af' => 'Afganistán',
            'am' => 'Armenia',
            'az' => 'Azerbaiyán',
            'bd' => 'Bangladés',
            'bt' => 'Bután',
            'ge' => 'Georgia',
            'kh' => 'Camboya',
            'kz' => 'Kazajistán',
            'kg' => 'Kirguistán',
            'la' => 'Laos',
            'mm' => 'Birmania',
            'mn' => 'Mongolia',
            'np' => 'Nepal',
            'lk' => 'Sri Lanka',
            'tj' => 'Tayikistán',
            'tm' => 'Turkmenistán',
            'uz' => 'Uzbekistán',
            'vn' => 'Vietnam',
            'au' => 'Australia',
            'nz' => 'Nueva Zelanda',
            'fj' => 'Fiyi',
            'pg' => 'Papúa Nueva Guinea',
            'sb' => 'Islas Salomón',
            'vu' => 'Vanuatu',
            'ws' => 'Samoa',
            'to' => 'Tonga',
            'tv' => 'Tuvalu',
            'ck' => 'Islas Cook',
            'nr' => 'Nauru',
            'ki' => 'Kiribati',
            'mh' => 'Islas Marshall',
            'pw' => 'Palaos',
            'ru' => 'Rusia',
        ];
        if ($tld === 'com') {
            return 'España';
        }
        return $map[$tld] ?? null;
    }

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
