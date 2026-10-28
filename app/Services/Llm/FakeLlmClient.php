<?php

namespace App\Services\Llm;

class FakeLlmClient implements LlmClient
{
    public function explainMiss(string $question, array $expectedIds, array $retrievedIds): string
    {
        return 'The top-k results did not include '.implode(', ', $expectedIds).'. Review chunk wording and query coverage.';
    }
}
