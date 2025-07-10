<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    protected function getClient()
    {
        return new Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'headers' => [
                'Authorization' => 'Bearer ' . config('services.openai.api_key'),
                'Content-Type'  => 'application/json',
            ],
        ]);
    }

    public function analizarSentimientoYTematica(string $texto)
    {
        try {
            $client = $this->getClient();

            $prompt = <<<PROMPT
Eres un analista experto en noticias. Tu tarea es analizar el siguiente texto y devolver estrictamente un JSON con esta estructura:

{
  "sentimiento": "positivo" | "negativo" | "neutro",
  "tematicas": ["tema1", "tema2", "tema3"]
}

No añadas explicaciones, encabezados ni texto adicional. Solo el JSON plano. Máximo 3 temáticas. El texto a analizar es:
PROMPT;

            $response = $client->post('chat/completions', [
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $prompt
                        ],
                        [
                            'role' => 'user',
                            'content' => $texto
                        ]
                    ],
                    'temperature' => 0.2,
                    'max_tokens' => 200,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $content = $data['choices'][0]['message']['content'] ?? null;

            if (! $content) {
                Log::warning("OpenAI no devolvió contenido en el análisis.");
                return null;
            }

            $parsed = json_decode($content, true);

            if (! is_array($parsed) || ! isset($parsed['sentimiento']) || ! isset($parsed['tematicas'])) {
                Log::warning("Formato de análisis inesperado: " . $content);
                return null;
            }

            return $parsed;
        } catch (\Exception $e) {
            Log::error('Error al analizar sentimiento y temática: ' . $e->getMessage());
            return null;
        }
    }


    public function generarDescripcionDesdeTitulo(string $titulo): ?string
    {
        try {
            $client = $this->getClient();

            $response = $client->post('chat/completions', [
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Eres un redactor profesional de noticias en español. Tu tarea es generar una breve descripción informativa basada en el título proporcionado. No repitas literalmente el título. Proporciona una frase original que resuma el contenido probable de la noticia basándote en el título proporcionado. Devuelve solo una frase, sin añadidos.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $titulo
                        ],
                    ],
                    'temperature' => 0.5,
                    'max_tokens' => 300,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['choices'][0]['message']['content'] ?? null;
        } catch (\Exception $e) {
            Log::error('Error al generar descripción desde título: ' . $e->getMessage());
            return null;
        }
    }

    public function traducirATextoEspanol(string $texto, string $idiomaOrigen): ?string
    {
        try {
            $client = $this->getClient();

            $response = $client->post('chat/completions', [
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => "Eres un traductor profesional de texto a español. Tu única tarea es traducir textos al español, sin añadir explicaciones, saludos, notas ni ningún tipo de introducción. Devuelve exclusivamente el texto traducido al español, sin comillas ni prefijos. Traduce al español el siguiente texto."
                        ],
                        [
                            'role' => 'user',
                            'content' => "Responde directamente este texto traducido al español, sin añadir absolutamente nada: {$texto}"
                        ]
                    ],
                    'temperature' => 0.6,
                    'max_tokens' => 700,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['choices'][0]['message']['content'] ?? null;
        } catch (\Exception $e) {
            Log::error('Error al traducir texto con OpenAI: ' . $e->getMessage());
            return null;
        }
    }


    public function inferirPaisDesdeTexto(string $texto)
    {
        try {
            $client = $this->getClient();

            $response = $client->post('chat/completions', [
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Eres un experto en geolocalización de noticias. Lee el texto y responde únicamente con el nombre del país del que parece provenir. Si no puedes saberlo, responde "Desconocido".'
                        ],
                        [
                            'role' => 'user',
                            'content' => "¿De qué país parece esta noticia? {$texto}"
                        ]
                    ],
                    'temperature' => 0.2,
                    'max_tokens' => 20,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $content = $data['choices'][0]['message']['content'] ?? '';

            $pais = trim($content);
            if (strtolower($pais) === 'desconocido' || strlen($pais) < 3) {
                return null;
            }

            return $pais;
        } catch (\Exception $e) {
            Log::error('Error en la inferencia de país con OpenRouter: ' . $e->getMessage());
            return null;
        }
    }
}
