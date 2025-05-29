<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    protected function getClient()
    {
        return new Client([
            'base_uri' => 'https://openrouter.ai/api/v1/',
            'headers' => [
                'Authorization' => 'Bearer ' . config('services.openrouter.api_key'),
                'Content-Type'  => 'application/json',
            ],
        ]);
    }

    public function analizarSentimientoYTematica(string $texto)
    {
        try {
            $client = $this->getClient();

            $response = $client->post('chat/completions', [
                'json' => [
                    'model' => 'openai/gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Eres un analista de sentimientos y temáticas de menciones en alertas. Analiza el texto y responde exclusivamente en formato JSON con los campos "sentimiento" y "tematicas".'
                        ],
                        [
                            'role' => 'user',
                            'content' => "Analiza el siguiente texto y responde en JSON: {$texto}"
                        ]
                    ],
                    'temperature' => 0.2,
                    'max_tokens' => 200,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $content = $data['choices'][0]['message']['content'] ?? null;
            $parsed = json_decode($content, true);

            return $parsed;
        } catch (\Exception $e) {
            Log::error('Error en la llamada a OpenRouter: ' . $e->getMessage());
            return null;
        }
    }

    public function generarDescripcionDesdeTitulo(string $titulo): ?string
    {
        try {
            $client = $this->getClient();

            $response = $client->post('chat/completions', [
                'json' => [
                    'model' => 'openai/gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Eres un redactor de noticias. Genera una breve descripción o resumen de una oración basado en el título proporcionado.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $titulo
                        ],
                    ],
                    'temperature' => 0.5,
                    'max_tokens' => 60,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['choices'][0]['message']['content'] ?? null;
        } catch (\Exception $e) {
            Log::error('Error al generar descripción desde título: ' . $e->getMessage());
            return null;
        }
    }

    public function inferirPaisDesdeTexto(string $texto)
    {
        try {
            $client = $this->getClient();

            $response = $client->post('chat/completions', [
                'json' => [
                    'model' => 'openai/gpt-3.5-turbo',
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
