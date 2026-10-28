<?php

namespace App\Http\Controllers;

use App\Models\EvalCase;
use App\Models\EvalRun;
use App\Models\FaqChunk;
use App\Services\Evaluation\EvalRunner;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EvalRunController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Welcome', $this->pageData(EvalRun::query()->latest('id')->first()));
    }

    public function store(Request $request, EvalRunner $runner): Response
    {
        $validated = $request->validate(['k' => ['required', 'integer', 'min:1', 'max:8']]);
        $run = $runner->run((int) $validated['k']);

        return Inertia::render('Welcome', $this->pageData($run));
    }

    /** @return array<string, mixed> */
    private function pageData(?EvalRun $run): array
    {
        return [
            'chunks' => FaqChunk::query()->orderBy('id')->get(['id', 'title', 'content']),
            'cases' => EvalCase::query()->orderBy('id')->get(['id', 'question', 'relevant_ids']),
            'run' => $run?->only(['id', 'k', 'recall', 'hits', 'misses', 'created_at']),
            'llmEnabled' => is_string(config('services.gemini.key')) && config('services.gemini.key') !== '',
        ];
    }
}
