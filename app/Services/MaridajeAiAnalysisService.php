<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use JsonException;
use Throwable;

class MaridajeAiAnalysisService
{
    /** Texto máximo enviado al modelo (optimización de tokens). */
    private const int AI_PROMPT_MAX_CHARS = 1500;

    /** Validación previa (posts pueden guardar descripciones más largas). */
    private const int MAX_DESCRIPTION_CHARS = 12000;

    /**
     * Invoca el modelo configurado y devuelve el JSON normalizado o null si falla / no hay API key.
     *
     * @return array{historia: string, afinidad: string, equilibrio: string, recomendacion: string, score: int}|null
     */
    public function analyzeDescription(?string $description): ?array
    {
        $apiKey = config('services.maridaje_ai.api_key');
        if (! is_string($apiKey) || $apiKey === '') {
            Log::warning('MaridajeAiAnalysisService: MARIDAJE_AI_API_KEY ausente o vacía en config (revisar .env y php artisan config:cache).');

            return null;
        }

        $text = trim(strip_tags((string) $description));
        if ($text === '' || mb_strlen($text) < 12) {
            Log::info('MaridajeAiAnalysisService: descripción vacía o demasiado corta para analizar.', [
                'length' => mb_strlen($text),
            ]);

            return null;
        }

        if (mb_strlen($text) > self::MAX_DESCRIPTION_CHARS) {
            $text = mb_substr($text, 0, self::MAX_DESCRIPTION_CHARS);
        }

        $text = Str::limit($text, self::AI_PROMPT_MAX_CHARS, '');

        try {
            $quotedDescription = json_encode($text, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            Log::warning('MaridajeAiAnalysisService: no se pudo codificar descripción para prompt.', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        $baseUrl = rtrim((string) config('services.maridaje_ai.base_url'), '/');
        $model = (string) config('services.maridaje_ai.model');
        $timeout = (int) config('services.maridaje_ai.timeout', 90);

        $userPrompt = <<<PROMPT
Analiza el siguiente maridaje gastronómico descrito en esta cadena JSON (texto plano del usuario):

{$quotedDescription}

Devuelve un JSON con:

* historia (máx 80 palabras)
* afinidad
* equilibrio
* recomendacion
* score (1 a 10)

No más de 250 palabras en total.
No inventar información.
Ser claro y técnico.
PROMPT;

        try {
            $response = Http::timeout($timeout)
                ->withToken($apiKey)
                ->acceptJson()
                ->post("{$baseUrl}/chat/completions", [
                    'model' => $model,
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.35,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Eres un sommelier y experto en maridaje gastronómico latinoamericano. Respondes únicamente JSON válido UTF-8 con las claves solicitadas en español.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $userPrompt,
                        ],
                    ],
                ]);
        } catch (Throwable $e) {
            Log::warning('MaridajeAiAnalysisService: fallo de red.', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('MaridajeAiAnalysisService: respuesta HTTP no exitosa.', [
                'status' => $response->status(),
                'body' => Str::limit($response->body(), 500),
            ]);

            return null;
        }

        /** @var array<string, mixed>|null $decoded */
        $decoded = $response->json();
        $content = data_get($decoded, 'choices.0.message.content');
        if (! is_string($content) || $content === '') {
            Log::warning('MaridajeAiAnalysisService: contenido vacío del modelo.');

            return null;
        }

        $parsed = $this->decodeModelJson($content);

        return $this->normalizePayload($parsed);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeModelJson(string $content): ?array
    {
        $trimmed = trim($content);
        $decoded = json_decode($trimmed, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{[\s\S]*\}/u', $trimmed, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        Log::warning('MaridajeAiAnalysisService: JSON inválido del modelo.', [
            'snippet' => Str::limit($trimmed, 400),
        ]);

        return null;
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array{historia: string, afinidad: string, equilibrio: string, recomendacion: string, score: int}|null
     */
    private function normalizePayload(array $raw): ?array
    {
        $historia = trim((string) ($raw['historia'] ?? ''));
        $afinidad = trim((string) ($raw['afinidad'] ?? ''));
        $equilibrio = trim((string) ($raw['equilibrio'] ?? ''));
        $recomendacion = trim((string) ($raw['recomendacion'] ?? ''));

        if ($historia === '' || $afinidad === '' || $equilibrio === '' || $recomendacion === '') {
            Log::warning('MaridajeAiAnalysisService: faltan campos obligatorios en JSON.');

            return null;
        }

        $scoreRaw = $raw['score'] ?? null;
        $score = is_numeric($scoreRaw) ? (int) round((float) $scoreRaw) : 0;
        $score = max(1, min(10, $score));

        return [
            'historia' => Str::limit($historia, 520),
            'afinidad' => Str::limit($afinidad, 400),
            'equilibrio' => Str::limit($equilibrio, 400),
            'recomendacion' => Str::limit($recomendacion, 400),
            'score' => $score,
        ];
    }
}
