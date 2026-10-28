# RAG Eval Desk

RAG Eval Desk is a small, recruiter-ready retrieval evaluation workbench. It measures whether a Harbor Desk FAQ search retrieves the chunks a human marked relevant, using a seeded gold set and **recall@k**. Retrieval and scoring are deterministic, so the same corpus and question produce the same ranking and score.

## What it demonstrates

- TF-IDF term weighting and cosine similarity over eight FAQ chunks.
- Five human-labeled questions, including questions with multiple relevant chunk IDs.
- Micro recall@k: relevant chunk IDs found in the top-k results divided by all relevant IDs in the gold set.
- A visible per-question report with expected IDs, ranked results, scores, hits, misses, and a concise miss explanation.
- An optional Gemini miss explainer behind `App\Services\Llm\LlmClient`. The LLM never chooses retrieval results or affects recall.

## Architecture

```mermaid
flowchart LR
    A[Gold set<br/>EvalCase questions + relevant_ids] --> B[TF-IDF cosine<br/>retrieve @ k]
    C[FAQ corpus<br/>8 seeded chunks] --> B
    B --> D[Recall@k<br/>hits / relevant IDs]
    D --> E[EvalRun<br/>recall, hits, misses]
    E --> F[Vue evaluation desk]
    B -. misses only .-> G{GEMINI_API_KEY?}
    G -->|No| H[Fake / deterministic fallback]
    G -->|Yes| I[Gemini<br/>12s timeout]
    H --> F
    I --> F
```

`TfidfRetriever` computes document frequencies across the current corpus, forms TF-IDF vectors for the query and chunks, and sorts by cosine similarity. Ties are broken by chunk ID so rankings remain stable. `EvalRunner` compares the top-k chunk IDs against each case's `relevant_ids`, persists the run, and asks the LLM interface for an explanation only when a relevant ID was missed.

## Run locally (no API key required)

Requirements: PHP 8.3+, Composer, Node.js 22+, and npm.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Open <http://localhost:8000>. The seeded demo corpus and gold set work without credentials. With no `GEMINI_API_KEY`, `FakeLlmClient` provides deterministic miss notes; retrieval and scoring work normally. To use Gemini for miss explanations, set `GEMINI_API_KEY` (and optionally `GEMINI_MODEL`) in `.env`. Requests have a 12-second timeout, and unavailable explanations fall back without interrupting a run.

`php artisan db:seed` safely upserts the eight FAQ chunks and five gold questions. No login, demo credentials, Redis, external vector database, or real customer data are required.

## Tests and checks

```bash
php artisan test
vendor/bin/pint --test
vendor/bin/phpstan analyse --memory-limit=512M
npm run check
npm run types:check
```

The Pest feature tests verify that an in-corpus gold answer is found in the top three, the LLM interface resolves to `FakeLlmClient` in tests, an unauthenticated visitor can create a run successfully, and an LLM failure does not break deterministic scoring.

## Stack

Laravel 13 · PHP 8.3 · Vue 3 · Inertia · Tailwind CSS · Pest
