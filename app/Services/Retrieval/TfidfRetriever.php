<?php

namespace App\Services\Retrieval;

use App\Models\FaqChunk;
use Illuminate\Support\Collection;

class TfidfRetriever
{
    /**
     * @param  Collection<int, FaqChunk>  $chunks
     * @return list<array{id: string, title: string, content: string, score: float}>
     */
    public function retrieve(string $query, Collection $chunks, int $k): array
    {
        $documents = $chunks->mapWithKeys(fn (FaqChunk $chunk): array => [
            $chunk->id => $this->termFrequencies($chunk->title.' '.$chunk->content),
        ]);
        $queryTerms = $this->termFrequencies($query);
        $documentFrequency = [];

        foreach ($documents as $terms) {
            foreach (array_keys($terms) as $term) {
                $documentFrequency[$term] = ($documentFrequency[$term] ?? 0) + 1;
            }
        }

        $totalDocuments = max($documents->count(), 1);
        $queryVector = $this->weightedVector($queryTerms, $documentFrequency, $totalDocuments);
        $ranked = [];

        foreach ($chunks as $chunk) {
            /** @var array<string, int> $terms */
            $terms = $documents->get($chunk->id, []);
            $documentVector = $this->weightedVector($terms, $documentFrequency, $totalDocuments);
            $ranked[] = [
                'id' => $chunk->id,
                'title' => $chunk->title,
                'content' => $chunk->content,
                'score' => $this->cosineSimilarity($queryVector, $documentVector),
            ];
        }

        usort($ranked, fn (array $left, array $right): int => ($right['score'] <=> $left['score']) ?: strcmp($left['id'], $right['id'])
        );

        return array_slice($ranked, 0, max(0, $k));
    }

    /** @return array<string, int> */
    private function termFrequencies(string $text): array
    {
        preg_match_all('/[a-z0-9]+/i', mb_strtolower($text), $matches);
        $stopWords = ['a', 'an', 'and', 'are', 'as', 'at', 'be', 'by', 'can', 'do', 'for', 'from', 'how', 'i', 'in', 'is', 'it', 'my', 'of', 'on', 'or', 'the', 'to', 'we', 'what', 'when', 'where', 'with', 'you'];
        $terms = [];

        foreach ($matches[0] as $term) {
            if (strlen($term) < 2 || in_array($term, $stopWords, true)) {
                continue;
            }

            $terms[$term] = ($terms[$term] ?? 0) + 1;
        }

        return $terms;
    }

    /** @param array<string, int> $terms
     * @param  array<string, int>  $documentFrequency
     * @return array<string, float>
     */
    private function weightedVector(array $terms, array $documentFrequency, int $totalDocuments): array
    {
        $vector = [];

        foreach ($terms as $term => $frequency) {
            $idf = log(1 + ($totalDocuments / (1 + ($documentFrequency[$term] ?? 0))));
            $vector[$term] = (1 + log($frequency)) * $idf;
        }

        return $vector;
    }

    /** @param array<string, float> $left
     * @param  array<string, float>  $right
     */
    private function cosineSimilarity(array $left, array $right): float
    {
        $dotProduct = 0.0;

        foreach ($left as $term => $weight) {
            $dotProduct += $weight * ($right[$term] ?? 0.0);
        }

        $leftMagnitude = sqrt(array_sum(array_map(fn (float $weight): float => $weight ** 2, $left)));
        $rightMagnitude = sqrt(array_sum(array_map(fn (float $weight): float => $weight ** 2, $right)));

        if ($leftMagnitude === 0.0 || $rightMagnitude === 0.0) {
            return 0.0;
        }

        return $dotProduct / ($leftMagnitude * $rightMagnitude);
    }
}
