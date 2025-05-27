<?php

namespace App\Console\Commands;

use Laminas\Feed\Reader\Reader;
use App\Models\Mencion;
use Illuminate\Console\Command;
use App\Services\OpenAIService;
use App\Models\Alerta;

class ProcesarMencionesRSS extends Command
{
    protected $signature = 'app:procesar-menciones-rss';
    protected $description = 'Procesa menciones desde los feeds RSS, evitando duplicados y completando el análisis (sentimiento y temática) en aquellas que aun no tienen datos.';

    public function handle()
    {
        $openAI = new OpenAIService();
        $alertas = Alerta::where('resuelta', false)->get(); 
        foreach ($alertas as $alerta) {
            $alertaId = $alerta->id;
            $url = $alerta->url;
            if (!$url) continue;

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

                $fuenteDominio = $this->extraerDominio($enlace);
                $fuentePais = $this->extraerPaisDeUrl($enlace); 
                if (!$fuentePais && $descripcion) {
                    $idioma = $this->detectarIdioma($titulo . ' ' . $descripcion);
                    $fuentePais = $this->asignarPaisPorIdioma($idioma, $fuenteDominio);
                }

                if (!$fuentePais && $descripcion) {
                    $contexto = "Dominio: {$fuenteDominio}. Título: {$titulo}. Descripción: {$descripcion}";
                    $paisInferido = app(OpenAIService::class)->inferirPaisDesdeTexto($contexto);
                    if ($paisInferido && strtolower($paisInferido) !== 'desconocido') {
                        $fuentePais = $paisInferido;
                    }
                }
                if ($fuentePais) {
                    $fuentePais = rtrim($fuentePais, ". ");
                }
                $fuente = $fuenteDominio;
                if ($fuentePais) {
                    $fuente .= ' - ' . $fuentePais;
                }

                if ($this->isDuplicateTitle($tituloNormalizado)) {
                    continue;
                }

                $mencion = Mencion::create([
                    'titulo'             => $titulo,
                    'titulo_normalizado' => $tituloNormalizado,
                    'enlace'             => $enlace,
                    'fuente'             => $fuente,
                    'fecha'              => $fecha,
                    'descripcion'        => $descripcion,
                    'alerta_id'          => $alertaId,
                ]);

                if (is_null($mencion->sentimiento) || is_null($mencion->tematica)) {
                    $this->analizarYActualizarMencion($mencion, $openAI);
                }
            }
        }

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

    protected function analizarYActualizarMencion($mencion, OpenAIService $openAI)
    {
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

    protected function extraerPaisDeUrl($enlace)
    {
        if (!$enlace) return null;
        $parsed = parse_url($enlace);
        if (!isset($parsed['host'])) return null;
        $host = $parsed['host'];
        // Extraer TLD
        if (preg_match('/\.([a-z]{2,3})(?:\.|$)/i', $host, $matches)) {
            $tld = strtolower($matches[1]);
            $paises = [
                'es' => 'España', 'fr' => 'Francia', 'it' => 'Italia', 'pt' => 'Portugal', 'de' => 'Alemania', 'uk' => 'Reino Unido', 'ie' => 'Irlanda', 'nl' => 'Países Bajos', 'be' => 'Bélgica', 'ch' => 'Suiza', 'at' => 'Austria', 'se' => 'Suecia', 'no' => 'Noruega', 'fi' => 'Finlandia', 'dk' => 'Dinamarca', 'pl' => 'Polonia', 'cz' => 'República Checa', 'gr' => 'Grecia', 'hu' => 'Hungría', 'ro' => 'Rumanía', 'bg' => 'Bulgaria', 'sk' => 'Eslovaquia', 'si' => 'Eslovenia', 'hr' => 'Croacia', 'rs' => 'Serbia', 'ua' => 'Ucrania', 'lt' => 'Lituania', 'lv' => 'Letonia', 'ee' => 'Estonia', 'lu' => 'Luxemburgo', 'md' => 'Moldavia', 'al' => 'Albania', 'by' => 'Bielorrusia', 'ba' => 'Bosnia y Herzegovina', 'mk' => 'Macedonia del Norte', 'mt' => 'Malta', 'mc' => 'Mónaco', 'li' => 'Liechtenstein', 'sm' => 'San Marino', 'va' => 'Vaticano', 'gi' => 'Gibraltar', 'is' => 'Islandia', 'tr' => 'Turquía',
                'us' => 'Estados Unidos', 'ca' => 'Canadá', 'mx' => 'México', 'ar' => 'Argentina', 'br' => 'Brasil', 'cl' => 'Chile', 'co' => 'Colombia', 'pe' => 'Perú', 've' => 'Venezuela', 'uy' => 'Uruguay', 'ec' => 'Ecuador', 'bo' => 'Bolivia', 'py' => 'Paraguay', 'cr' => 'Costa Rica', 'do' => 'República Dominicana', 'cu' => 'Cuba', 'gt' => 'Guatemala', 'hn' => 'Honduras', 'ni' => 'Nicaragua', 'sv' => 'El Salvador', 'pa' => 'Panamá', 'pr' => 'Puerto Rico', 'jm' => 'Jamaica', 'tt' => 'Trinidad y Tobago', 'bs' => 'Bahamas', 'bb' => 'Barbados', 'ag' => 'Antigua y Barbuda', 'dm' => 'Dominica', 'gd' => 'Granada', 'kn' => 'San Cristóbal y Nieves', 'lc' => 'Santa Lucía', 'vc' => 'San Vicente y las Granadinas', 'sr' => 'Surinam', 'gy' => 'Guyana', 'bz' => 'Belice',
                'za' => 'Sudáfrica', 'ng' => 'Nigeria', 'eg' => 'Egipto', 'ma' => 'Marruecos', 'dz' => 'Argelia', 'tn' => 'Túnez', 'ke' => 'Kenia', 'gh' => 'Ghana', 'et' => 'Etiopía', 'sn' => 'Senegal', 'ug' => 'Uganda', 'zm' => 'Zambia', 'zw' => 'Zimbabue', 'ao' => 'Angola', 'cm' => 'Camerún', 'ci' => 'Costa de Marfil', 'mg' => 'Madagascar', 'mw' => 'Malaui', 'mz' => 'Mozambique', 'na' => 'Namibia', 'bw' => 'Botsuana', 'bf' => 'Burkina Faso', 'cd' => 'República Democrática del Congo', 'cg' => 'República del Congo', 'ga' => 'Gabón', 'gm' => 'Gambia', 'gn' => 'Guinea', 'gw' => 'Guinea-Bisáu', 'lr' => 'Liberia', 'ml' => 'Malí', 'mr' => 'Mauritania', 'ne' => 'Níger', 'rw' => 'Ruanda', 'sc' => 'Seychelles', 'sl' => 'Sierra Leona', 'so' => 'Somalia', 'sd' => 'Sudán', 'sz' => 'Suazilandia', 'tg' => 'Togo', 'td' => 'Chad', 'cf' => 'República Centroafricana', 'dj' => 'Yibuti', 'er' => 'Eritrea', 'ls' => 'Lesoto', 'st' => 'Santo Tomé y Príncipe',
                'cn' => 'China', 'jp' => 'Japón', 'in' => 'India', 'kr' => 'Corea del Sur', 'sg' => 'Singapur', 'th' => 'Tailandia', 'id' => 'Indonesia', 'my' => 'Malasia', 'ph' => 'Filipinas', 'hk' => 'Hong Kong', 'tw' => 'Taiwán', 'il' => 'Israel', 'sa' => 'Arabia Saudita', 'ae' => 'Emiratos Árabes Unidos', 'qa' => 'Qatar', 'pk' => 'Pakistán', 'ir' => 'Irán', 'iq' => 'Irak', 'jo' => 'Jordania', 'kw' => 'Kuwait', 'lb' => 'Líbano', 'om' => 'Omán', 'sy' => 'Siria', 'ye' => 'Yemen', 'af' => 'Afganistán', 'am' => 'Armenia', 'az' => 'Azerbaiyán', 'bd' => 'Bangladés', 'bt' => 'Bután', 'ge' => 'Georgia', 'kh' => 'Camboya', 'kz' => 'Kazajistán', 'kg' => 'Kirguistán', 'la' => 'Laos', 'mm' => 'Birmania', 'mn' => 'Mongolia', 'np' => 'Nepal', 'lk' => 'Sri Lanka', 'tj' => 'Tayikistán', 'tm' => 'Turkmenistán', 'uz' => 'Uzbekistán', 'vn' => 'Vietnam',
                'au' => 'Australia', 'nz' => 'Nueva Zelanda', 'fj' => 'Fiyi', 'pg' => 'Papúa Nueva Guinea', 'sb' => 'Islas Salomón', 'vu' => 'Vanuatu', 'ws' => 'Samoa', 'to' => 'Tonga', 'tv' => 'Tuvalu', 'ck' => 'Islas Cook', 'nr' => 'Nauru', 'ki' => 'Kiribati', 'mh' => 'Islas Marshall', 'pw' => 'Palaos',
                'ru' => 'Rusia',
            ];
            if (isset($paises[$tld])) {
                return $paises[$tld];
            }
        }
        return null;
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

    protected function detectarIdioma($texto)
    {
        if (class_exists('Locale')) {
            $locale = \Locale::acceptFromHttp($texto);
            if ($locale) {
                return substr($locale, 0, 2);
            }
        }
        if (preg_match('/\b(el|la|de|que|y|en|los|se|del|las|por|un|para|con|no|una|su|al|lo|como|más|pero|sus|le|ya|o|este|sí|porque|esta|entre|cuando|muy|sin|sobre|también|me|hasta|hay|donde|quien|desde|todo|nos|durante|todos|uno|les|ni|contra|otros|ese|eso|ante|ellos|e|esto|mí|antes|algunos|qué|unos|yo|otro|otras|otra|él|tanto|esa|estos|mucho|quienes|nada|muchos|cual|poco|ella|estar|estas|algunas|algo|nosotros|mi|mis|tú|te|ti|tu|tus|ellas|nosotras|vosotros|vosotras|os|mío|mía|míos|mías|tuyo|tuya|tuyos|tuyas|suyo|suya|suyos|suyas|nuestro|nuestra|nuestros|nuestras|vuestro|vuestra|vuestros|vuestras|esos|esas|estoy|estás|está|estamos|estáis|están|esté|estés|estemos|estéis|estén|estaré|estarás|estará|estaremos|estaréis|estarán|estaría|estarías|estaríamos|estaríais|estarían|estaba|estabas|estábamos|estabais|estaban|estuve|estuviste|estuvo|estuvimos|estuvisteis|estuvieron|estuviera|estuvieras|estuviéramos|estuvierais|estuvieran|estuviese|estuvieses|estuviésemos|estuvieseis|estuviesen|estando|estado|estada|estados|estadas|estad)\b/i', $texto)) {
            return 'es';
        }
        return null;
    }


    protected function asignarPaisPorIdioma($idioma, $dominio)
    {
        $latam = [
            'ar' => 'Argentina',
            'mx' => 'México',
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
        ];
        if (preg_match('/\.([a-z]{2,3})$/i', $dominio, $matches)) {
            $tld = strtolower($matches[1]);
            if (isset($latam[$tld])) {
                return $latam[$tld];
            }
        }
        // Si idioma español y no hay dominio latinoamericano, prioriza España
        if ($idioma === 'es') {
            return 'España';
        }
        if ($idioma === 'fr') {
            return 'Francia';
        }
        if ($idioma === 'pt') {
            return 'Portugal';
        }
        if ($idioma === 'en') {
            return 'Estados Unidos';
        }
        if ($idioma === 'de') {
            return 'Alemania';
        }
        if ($idioma === 'it') {
            return 'Italia';
        }
        if ($idioma === 'ru') {
            return 'Rusia';
        }
        if ($idioma === 'cn') {
            return 'China';
        }
        if ($idioma === 'jp') {
            return 'Japón';
        }
        if ($idioma === 'in') {
            return 'India';
        }
        if ($idioma === 'au') {
            return 'Australia';
        }
        if ($idioma === 'ch') {
            return 'Suiza';
        }
        if ($idioma === 'be') {
            return 'Bélgica';
        }
        if ($idioma === 'nl') {
            return 'Países Bajos';
        }
        if ($idioma === 'se') {
            return 'Suecia';
        }
        if ($idioma === 'no') {
            return 'Noruega';
        }
        if ($idioma === 'fi') {
            return 'Finlandia';
        }
        if ($idioma === 'dk') {
            return 'Dinamarca';
        }
        if ($idioma === 'ie') {
            return 'Irlanda';
        }
        if ($idioma === 'pl') {
            return 'Polonia';
        }
        if ($idioma === 'cz') {
            return 'República Checa';
        }
        if ($idioma === 'at') {
            return 'Austria';
        }
        if ($idioma === 'gr') {
            return 'Grecia';
        }
        if ($idioma === 'tr') {
            return 'Turquía';
        }
        if ($idioma === 'za') {
            return 'Sudáfrica';
        }
        return null;
    }
}
