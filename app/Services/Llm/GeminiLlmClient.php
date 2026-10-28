<?php

namespace App\Services\Llm;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiLlmClient implements LlmClient
{
    public function __construct(private readonly string $apiKey, private readonly string $model) {}

    public function explainMiss(string $question, array $expectedIds, array $retrievedIds): string
    {
        $response = Http::timeout(12)
            ->connectTimeout(4)
            ->acceptJson()
            ->post('https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode($this->model).':generateContent?key='.rawurlencode($this->apiKey), [
                'contents' => [[
                    'parts' => [[
                        'text' => "In one concise sentence, explain this retrieval miss without inventing facts. Question: {$question}. Expected chunk ids: ".implode(', ', $expectedIds).'. Retrieved chunk ids: '.implode(', ', $retrievedIds).'.',
                    ]],
                ]],
                'generationConfig' => ['temperature' => 0.1, 'maxOutputTokens' => 90],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Gemini returned an unsuccessful response.');
        }

        $explanation = $response->json('candidates.0.content.parts.0.text');

        if (! is_string($explanation) || trim($explanation) === '') {
            throw new RuntimeException('Gemini returned no explanation.');
        }

        return trim($explanation);
    }
}
