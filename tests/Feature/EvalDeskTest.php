<?php

use App\Models\EvalRun;
use App\Services\Evaluation\EvalRunner;
use App\Services\Llm\FakeLlmClient;
use App\Services\Llm\LlmClient;
use Database\Seeders\EvalDeskSeeder;

beforeEach(function () {
    $this->seed(EvalDeskSeeder::class);
});

it('gets full recall for an in-corpus gold answer at k three', function () {
    $run = app(EvalRunner::class)->run(3);
    $trackingCase = collect($run->hits)->firstWhere('question', 'Where can I find the tracking link for my parcel?');

    expect($trackingCase['hit_ids'])->toBe($trackingCase['relevant_ids'])
        ->and($trackingCase['hit_ids'])->toContain('track-parcel');
});

it('binds the fake llm client for deterministic tests', function () {
    expect(app(LlmClient::class))->toBeInstanceOf(FakeLlmClient::class);
});

it('allows a guest to create an evaluation run with a successful response', function () {
    $this->post('/eval-runs', ['k' => 3])->assertOk();

    expect(EvalRun::query()->count())->toBe(1);
});

it('keeps scoring successful when miss explanations fail', function () {
    app()->bind(LlmClient::class, fn () => new class implements LlmClient
    {
        public function explainMiss(string $question, array $expectedIds, array $retrievedIds): string
        {
            throw new RuntimeException('LLM unavailable');
        }
    });

    $run = app(EvalRunner::class)->run(1);

    expect($run->recall)->toBeGreaterThanOrEqual(0.0)
        ->and(collect($run->misses)->every(fn (array $miss): bool => str_contains($miss['explanation'], 'unavailable')))
        ->toBeTrue();
});
