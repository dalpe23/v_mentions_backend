<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    public function analizarSentimientoYTematica(string $texto)
    {
        try {
            $client = new Client([
                'base_uri' => 'https://openrouter.ai/api/v1/',
                'headers' => [
                    'Authorization' => 'Bearer ' . config('services.openrouter.api_key'),
                    'Content-Type'  => 'application/json',
                ],
            ]);

            $response = $client->post('chat/completions', [
                'json' => [
                    'model' => 'openai/gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Eres un analista de sentimientos y temáticas de menciones en alertas, leerás su texto y determinarás si tienen en ellas algo positivo, negativo o si es de sentimiento neutro. Responde únicamente en formato JSON con los campos "sentimiento" y "tematicas".'
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

            return $content ? json_decode($content, true) : null;

        } catch (\Exception $e) {
            Log::error('Error en la llamada a OpenRouter: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Intenta inferir el país de una noticia a partir de su texto usando OpenAI.
     * Devuelve el nombre del país o null si no se puede determinar.
     */
    public function inferirPaisDesdeTexto(string $texto)
    {
        try {
            $client = new Client([
                'base_uri' => 'https://openrouter.ai/api/v1/',
                'headers' => [
                    'Authorization' => 'Bearer ' . config('services.openrouter.api_key'),
                    'Content-Type'  => 'application/json',
                ],
            ]);

            $response = $client->post('chat/completions', [
                'json' => [
                    'model' => 'openai/gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Eres un experto en geolocalización de noticias. Lee el texto de una noticia y responde únicamente el nombre del país de donde parece provenir la noticia. Si no puedes determinarlo, responde solo "Desconocido".'
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
            $content = $data['choices'][0]['message']['content'] ?? null;
            $pais = trim($content);
            if (strtolower($pais) === 'desconocido') {
                return null;
            }
            return $pais;
        } catch (\Exception $e) {
            Log::error('Error en la inferencia de país con OpenRouter: ' . $e->getMessage());
            return null;
        }
    }
}

