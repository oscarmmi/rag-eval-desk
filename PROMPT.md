Build RAG Eval Desk in THIS folder: C:\xampp\htdocs\rag-eval-desk

A recruiter-ready demo that scores retrieval quality (recall@k) on a tiny Harbor Desk FAQ gold set.

Laravel 13 + Vue 3 starter kit is already here if present. Reuse it. Do not create a second app inside NewPortfolio.

## Must satisfy

- Models: `EvalCase` (question, relevant_ids json), `EvalRun` (k, recall, hits json, misses json).
- Deterministic TF-IDF cosine retrieval. Compute recall@k against gold relevant chunk ids. Optional LLM only to explain a miss. Timeout 12s. Never 500.
- `App\Services\Llm\LlmClient` with `FakeLlmClient` in tests and when no `GEMINI_API_KEY`. Optional Gemini when key is set.
- Seed 8 FAQ chunks and 5 gold questions (some with 2 relevant ids). English only.
- UI: run eval, show recall@k, per-question hits/misses.
- Pest: a gold question with an in-corpus answer has recall 1 at k=3; FakeLlm bound; guest POST 200.
- README mermaid: Gold set → retrieve@k → recall. How to run without API key.

## GitHub

- git config user.name "Oscar Munoz" / user.email "oscarxxi@gmail.com"
- Commit: feat: add rag eval desk
- Public https://github.com/oscarmmi/rag-eval-desk
- gh repo edit --description "Score FAQ retrieval with recall@k on a Harbor Desk gold set (Laravel + Vue)." --add-topic laravel --add-topic vue --add-topic ai --add-topic php --add-topic rag
- Stop. Print the URL.

No Impeccable. No Redis. No real PHI.
