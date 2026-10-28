<?php

namespace App\Services\Llm;

interface LlmClient
{
    /** @param list<string> $expectedIds
     * @param  list<string>  $retrievedIds
     */
    public function explainMiss(string $question, array $expectedIds, array $retrievedIds): string;
}
