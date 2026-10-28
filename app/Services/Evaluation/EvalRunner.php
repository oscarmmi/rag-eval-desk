<?php

namespace App\Services\Evaluation;

use App\Models\EvalCase;
use App\Models\EvalRun;
use App\Models\FaqChunk;
use App\Services\Llm\LlmClient;
use App\Services\Retrieval\TfidfRetriever;
use Throwable;

class EvalRunner
{
    public function __construct(
        private readonly TfidfRetriever $retriever,
        private readonly LlmClient $llm,
    ) {}

    public function run(int $k): EvalRun
    {
        $chunks = FaqChunk::query()->get();
        $cases = EvalCase::query()->orderBy('id')->get();
        $hits = [];
        $misses = [];
        $totalRelevant = 0;
        $totalHits = 0;

        foreach ($cases as $case) {
            $relevantIds = $case->relevantIds();
            $retrieved = $this->retriever->retrieve($case->question, $chunks, $k);
            $retrievedIds = array_column($retrieved, 'id');
            $hitIds = array_values(array_intersect($relevantIds, $retrievedIds));
            $missingIds = array_values(array_diff($relevantIds, $hitIds));
            $totalRelevant += count($relevantIds);
            $totalHits += count($hitIds);

            $hits[] = [
                'question' => $case->question,
                'relevant_ids' => $relevantIds,
                'hit_ids' => $hitIds,
                'retrieved' => $retrieved,
            ];

            if ($missingIds !== []) {
                try {
                    $explanation = $this->llm->explainMiss($case->question, $missingIds, $retrievedIds);
                } catch (Throwable) {
                    $explanation = 'Explanation unavailable. The deterministic retrieval score is still valid.';
                }

                $misses[] = [
                    'question' => $case->question,
                    'ids' => $missingIds,
                    'explanation' => $explanation,
                ];
            }
        }

        return EvalRun::query()->create([
            'k' => $k,
            'recall' => $totalRelevant === 0 ? 0.0 : $totalHits / $totalRelevant,
            'hits' => $hits,
            'misses' => $misses,
        ]);
    }
}
