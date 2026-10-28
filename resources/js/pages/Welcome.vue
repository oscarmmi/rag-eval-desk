<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Chunk {
    id: string;
    title: string;
    content: string;
}

interface EvalCase {
    id: number;
    question: string;
    relevant_ids: string[];
}

interface RetrievedChunk extends Chunk {
    score: number;
}

interface RunHit {
    question: string;
    relevant_ids: string[];
    hit_ids: string[];
    retrieved: RetrievedChunk[];
}

interface RunMiss {
    question: string;
    ids: string[];
    explanation: string;
}

interface EvalRun {
    id: number;
    k: number;
    recall: number;
    hits: RunHit[];
    misses: RunMiss[];
    created_at: string;
}

const props = defineProps<{
    chunks: Chunk[];
    cases: EvalCase[];
    run: EvalRun | null;
    llmEnabled: boolean;
}>();

const form = useForm({ k: 3 });
const recallPercent = computed(() =>
    props.run ? Math.round(props.run.recall * 100) : null,
);
const lastUpdated = computed(() => {
    if (!props.run) return 'Not run yet';
    return new Intl.DateTimeFormat('en', {
        hour: 'numeric',
        minute: '2-digit',
    }).format(new Date(props.run.created_at));
});

function missFor(question: string): RunMiss | undefined {
    return props.run?.misses.find((miss) => miss.question === question);
}

function runEvaluation() {
    form.post('/eval-runs', { preserveScroll: true });
}
</script>

<template>
    <Head title="RAG Eval Desk" />

    <div class="min-h-screen bg-[#f5f6f3] text-[#17221f]">
        <header class="border-b border-[#e4e8e2] bg-white">
            <div
                class="mx-auto flex max-w-[1440px] items-center justify-between px-6 py-4 lg:px-10"
            >
                <a
                    href="/"
                    class="flex items-center gap-3"
                    aria-label="Harbor Desk Eval home"
                >
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#183a32] text-[#d4f46c]"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            class="h-5 w-5"
                            aria-hidden="true"
                        >
                            <path
                                d="M4 17.5 12 4l8 13.5H4Z"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linejoin="round"
                            />
                            <path
                                d="M8 17.5 12 11l4 6.5"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </span>
                    <span class="leading-tight">
                        <span class="block text-sm font-semibold tracking-tight"
                            >Harbor Desk</span
                        >
                        <span
                            class="block text-[11px] font-medium tracking-wide text-[#76817c]"
                            >RETRIEVAL EVAL</span
                        >
                    </span>
                </a>

                <div
                    class="hidden items-center gap-2 rounded-full border border-[#e2e7df] bg-[#f8faf6] px-3 py-1.5 text-xs text-[#53605a] sm:flex"
                >
                    <span class="h-2 w-2 rounded-full bg-[#80b84b]"></span>
                    Corpus ready
                    <span class="mx-1 h-3 w-px bg-[#dce2da]"></span>
                    {{ chunks.length }} chunks
                </div>
                <a
                    href="https://github.com/oscarmmi/rag-eval-desk"
                    target="_blank"
                    rel="noreferrer"
                    class="text-sm font-medium text-[#56625c] transition hover:text-[#183a32]"
                >
                    Project notes <span aria-hidden="true">↗</span>
                </a>
            </div>
        </header>

        <main class="mx-auto max-w-[1440px] px-6 py-8 lg:px-10 lg:py-10">
            <section
                class="mb-8 flex flex-col justify-between gap-6 md:flex-row md:items-end"
            >
                <div class="max-w-2xl">
                    <div
                        class="mb-3 flex items-center gap-2 text-xs font-semibold tracking-[0.15em] text-[#78837c] uppercase"
                    >
                        <span class="h-px w-6 bg-[#a0ad9f]"></span>
                        Quality workspace
                        <span class="text-[#c0c8c0]">/</span> FAQ retrieval
                    </div>
                    <h1
                        class="text-3xl font-semibold tracking-[-0.04em] text-[#17221f] sm:text-4xl"
                    >
                        Does your search find the right answer?
                    </h1>
                    <p
                        class="mt-3 max-w-xl text-[15px] leading-6 text-[#68736d]"
                    >
                        Run a small, repeatable gold-set evaluation against
                        Harbor Desk’s support knowledge base.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <label
                        for="top-k"
                        class="text-sm font-medium text-[#59655f]"
                        >Retrieve top</label
                    >
                    <select
                        id="top-k"
                        v-model.number="form.k"
                        class="h-11 rounded-lg border border-[#dce2da] bg-white px-3 text-sm font-semibold text-[#24332d] shadow-sm outline-none focus:border-[#789e51] focus:ring-2 focus:ring-[#d5e8bc]"
                    >
                        <option :value="1">k = 1</option>
                        <option :value="3">k = 3</option>
                        <option :value="5">k = 5</option>
                        <option :value="8">k = 8</option>
                    </select>
                    <button
                        type="button"
                        :disabled="form.processing || cases.length === 0"
                        @click="runEvaluation"
                        class="inline-flex h-11 items-center gap-2 rounded-lg bg-[#183a32] px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-[#245044] disabled:cursor-wait disabled:opacity-60"
                    >
                        <svg
                            v-if="form.processing"
                            class="h-4 w-4 animate-spin"
                            viewBox="0 0 24 24"
                            fill="none"
                            aria-hidden="true"
                        >
                            <circle
                                cx="12"
                                cy="12"
                                r="9"
                                stroke="currentColor"
                                stroke-opacity=".3"
                                stroke-width="3"
                            />
                            <path
                                d="M21 12a9 9 0 0 0-9-9"
                                stroke="currentColor"
                                stroke-width="3"
                            />
                        </svg>
                        <svg
                            v-else
                            viewBox="0 0 24 24"
                            fill="none"
                            class="h-4 w-4"
                            aria-hidden="true"
                        >
                            <path d="m8 5 11 7-11 7V5Z" fill="currentColor" />
                        </svg>
                        {{ form.processing ? 'Evaluating…' : 'Run evaluation' }}
                    </button>
                </div>
            </section>

            <div
                v-if="form.errors.k"
                class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
            >
                {{ form.errors.k }}
            </div>

            <section
                class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
                aria-label="Evaluation summary"
            >
                <article
                    class="rounded-xl border border-[#e2e7df] bg-white p-5 shadow-[0_2px_8px_rgba(21,42,31,0.025)]"
                >
                    <div class="flex items-start justify-between">
                        <p class="text-sm font-medium text-[#66726b]">
                            Recall@{{ run?.k ?? form.k }}
                        </p>
                        <span
                            class="rounded-md bg-[#eff5e8] px-2 py-1 text-[10px] font-bold tracking-wider text-[#557b39] uppercase"
                            >Gold set</span
                        >
                    </div>
                    <p
                        class="mt-4 text-[34px] leading-none font-semibold tracking-[-0.045em]"
                    >
                        {{ recallPercent !== null ? `${recallPercent}%` : '—' }}
                    </p>
                    <div
                        class="mt-4 h-1.5 overflow-hidden rounded-full bg-[#edf0eb]"
                    >
                        <div
                            class="h-full rounded-full bg-[#8ab94c] transition-all duration-500"
                            :style="{ width: `${recallPercent ?? 0}%` }"
                        ></div>
                    </div>
                </article>
                <article
                    class="rounded-xl border border-[#e2e7df] bg-white p-5 shadow-[0_2px_8px_rgba(21,42,31,0.025)]"
                >
                    <p class="text-sm font-medium text-[#66726b]">
                        Test questions
                    </p>
                    <p
                        class="mt-4 text-[34px] leading-none font-semibold tracking-[-0.045em]"
                    >
                        {{ cases.length
                        }}<span class="ml-1 text-lg font-medium text-[#9aa39d]"
                            >cases</span
                        >
                    </p>
                    <p class="mt-3 text-xs text-[#87918a]">
                        Hand-labeled answers for repeatable checks
                    </p>
                </article>
                <article
                    class="rounded-xl border border-[#e2e7df] bg-white p-5 shadow-[0_2px_8px_rgba(21,42,31,0.025)]"
                >
                    <p class="text-sm font-medium text-[#66726b]">
                        Knowledge chunks
                    </p>
                    <p
                        class="mt-4 text-[34px] leading-none font-semibold tracking-[-0.045em]"
                    >
                        {{ chunks.length
                        }}<span class="ml-1 text-lg font-medium text-[#9aa39d]"
                            >FAQ docs</span
                        >
                    </p>
                    <p class="mt-3 text-xs text-[#87918a]">
                        Indexed with deterministic TF-IDF cosine
                    </p>
                </article>
                <article
                    class="rounded-xl border border-[#e2e7df] bg-white p-5 shadow-[0_2px_8px_rgba(21,42,31,0.025)]"
                >
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-[#66726b]">
                            Last evaluation
                        </p>
                        <span
                            class="flex items-center gap-1.5 text-[11px] font-medium"
                            :class="
                                llmEnabled ? 'text-[#527e42]' : 'text-[#718078]'
                            "
                            ><span
                                class="h-1.5 w-1.5 rounded-full"
                                :class="
                                    llmEnabled ? 'bg-[#78aa4c]' : 'bg-[#a7b0aa]'
                                "
                            ></span
                            >{{
                                llmEnabled ? 'Gemini enabled' : 'Offline mode'
                            }}</span
                        >
                    </div>
                    <p
                        class="mt-4 text-[25px] leading-none font-semibold tracking-[-0.035em]"
                    >
                        {{ lastUpdated }}
                    </p>
                    <p class="mt-3 text-xs text-[#87918a]">
                        {{
                            run
                                ? `Run #${run.id} · top ${run.k}`
                                : 'Run an evaluation to start'
                        }}
                    </p>
                </article>
            </section>

            <section
                class="mt-8 grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_340px]"
            >
                <div
                    class="overflow-hidden rounded-xl border border-[#e2e7df] bg-white shadow-[0_2px_8px_rgba(21,42,31,0.025)]"
                >
                    <div
                        class="flex flex-col justify-between gap-3 border-b border-[#edf0eb] px-5 py-4 sm:flex-row sm:items-center sm:px-6"
                    >
                        <div>
                            <h2 class="text-base font-semibold tracking-tight">
                                Gold-set results
                            </h2>
                            <p class="mt-1 text-xs text-[#87918a]">
                                Expected chunks compared with the top-{{
                                    run?.k ?? form.k
                                }}
                                retrievals
                            </p>
                        </div>
                        <span
                            v-if="run"
                            class="w-fit rounded-full border border-[#e1e8dc] bg-[#f7faf4] px-2.5 py-1 text-[11px] font-medium text-[#5e6b62]"
                            >{{
                                run.hits.reduce(
                                    (total, row) => total + row.hit_ids.length,
                                    0,
                                )
                            }}
                            /
                            {{
                                run.hits.reduce(
                                    (total, row) =>
                                        total + row.relevant_ids.length,
                                    0,
                                )
                            }}
                            relevant chunks found</span
                        >
                        <span
                            v-else
                            class="w-fit rounded-full border border-[#e8ebe7] bg-[#f7f8f6] px-2.5 py-1 text-[11px] font-medium text-[#738078]"
                            >Ready to evaluate</span
                        >
                    </div>

                    <div
                        v-if="!run"
                        class="flex min-h-64 flex-col items-center justify-center px-6 py-12 text-center"
                    >
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#eff5e8] text-[#507744]"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                class="h-6 w-6"
                                aria-hidden="true"
                            >
                                <path
                                    d="M4 19V5m0 14h16M7 15l4-5 3 2 5-7"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold">
                            Your evaluation report will appear here
                        </h3>
                        <p
                            class="mt-2 max-w-sm text-sm leading-5 text-[#7a857e]"
                        >
                            The runner retrieves FAQ chunks for every gold
                            question, then scores how many expected answers made
                            the top-k.
                        </p>
                    </div>

                    <div v-else class="divide-y divide-[#edf0eb]">
                        <article
                            v-for="(row, index) in run.hits"
                            :key="row.question"
                            class="px-5 py-5 sm:px-6"
                        >
                            <div class="flex items-start gap-3">
                                <span
                                    class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-[#f1f3ef] text-[11px] font-semibold text-[#748078]"
                                    >{{
                                        String(index + 1).padStart(2, '0')
                                    }}</span
                                >
                                <div class="min-w-0 flex-1">
                                    <div
                                        class="flex flex-wrap items-center justify-between gap-2"
                                    >
                                        <h3
                                            class="text-sm leading-5 font-semibold text-[#26332d]"
                                        >
                                            {{ row.question }}
                                        </h3>
                                        <span
                                            class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2 py-1 text-[10px] font-bold tracking-wide uppercase"
                                            :class="
                                                row.hit_ids.length ===
                                                row.relevant_ids.length
                                                    ? 'bg-[#eff6e9] text-[#537d3d]'
                                                    : 'bg-[#fff4e9] text-[#a66b2e]'
                                            "
                                        >
                                            <span
                                                class="h-1.5 w-1.5 rounded-full"
                                                :class="
                                                    row.hit_ids.length ===
                                                    row.relevant_ids.length
                                                        ? 'bg-[#80ae4d]'
                                                        : 'bg-[#d99a50]'
                                                "
                                            ></span>
                                            {{ row.hit_ids.length }}/{{
                                                row.relevant_ids.length
                                            }}
                                            hit{{
                                                row.relevant_ids.length === 1
                                                    ? ''
                                                    : 's'
                                            }}
                                        </span>
                                    </div>

                                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                        <div
                                            class="rounded-lg border border-[#e7ece3] bg-[#fafbf9] p-3"
                                        >
                                            <p
                                                class="mb-2 text-[10px] font-bold tracking-[0.12em] text-[#87918a] uppercase"
                                            >
                                                Relevant gold chunks
                                            </p>
                                            <div class="flex flex-wrap gap-1.5">
                                                <span
                                                    v-for="id in row.relevant_ids"
                                                    :key="id"
                                                    class="rounded-md border px-2 py-1 font-mono text-[10px]"
                                                    :class="
                                                        row.hit_ids.includes(id)
                                                            ? 'border-[#d8e7c9] bg-[#f1f7e9] text-[#4f743b]'
                                                            : 'border-[#f0ddc5] bg-[#fff7ed] text-[#a26c36]'
                                                    "
                                                    >{{ id }}</span
                                                >
                                            </div>
                                        </div>
                                        <div
                                            class="rounded-lg border border-[#e7ece3] bg-[#fafbf9] p-3"
                                        >
                                            <p
                                                class="mb-2 text-[10px] font-bold tracking-[0.12em] text-[#87918a] uppercase"
                                            >
                                                Retrieved top-{{ run.k }}
                                            </p>
                                            <div
                                                v-if="row.retrieved.length"
                                                class="space-y-1.5"
                                            >
                                                <div
                                                    v-for="(
                                                        chunk, rank
                                                    ) in row.retrieved"
                                                    :key="chunk.id"
                                                    class="flex items-center justify-between gap-2 text-[11px]"
                                                >
                                                    <span
                                                        class="min-w-0 truncate"
                                                        :class="
                                                            row.relevant_ids.includes(
                                                                chunk.id,
                                                            )
                                                                ? 'font-semibold text-[#4f743b]'
                                                                : 'text-[#647068]'
                                                        "
                                                        ><span
                                                            class="mr-1.5 font-mono text-[#9aa39d]"
                                                            >{{
                                                                rank + 1
                                                            }}</span
                                                        >{{ chunk.title }}</span
                                                    >
                                                    <span
                                                        class="shrink-0 font-mono text-[#87918a]"
                                                        >{{
                                                            chunk.score.toFixed(
                                                                2,
                                                            )
                                                        }}</span
                                                    >
                                                </div>
                                            </div>
                                            <p
                                                v-else
                                                class="text-[11px] text-[#9aa39d]"
                                            >
                                                No chunks in corpus
                                            </p>
                                        </div>
                                    </div>
                                    <div
                                        v-if="missFor(row.question)"
                                        class="mt-2 flex gap-2 rounded-lg bg-[#fff9f1] px-3 py-2.5 text-[11px] leading-4 text-[#85643e]"
                                    >
                                        <svg
                                            class="mt-0.5 h-3.5 w-3.5 shrink-0"
                                            viewBox="0 0 16 16"
                                            fill="none"
                                            aria-hidden="true"
                                        >
                                            <path
                                                d="M8 5v3m0 2h.01M7.14 2.4 1.8 11.65a1 1 0 0 0 .87 1.5h10.66a1 1 0 0 0 .87-1.5L8.86 2.4a1 1 0 0 0-1.72 0Z"
                                                stroke="currentColor"
                                                stroke-width="1.3"
                                                stroke-linecap="round"
                                            />
                                        </svg>
                                        <span
                                            ><strong class="font-semibold"
                                                >Miss analysis:</strong
                                            >
                                            {{
                                                missFor(row.question)
                                                    ?.explanation
                                            }}</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>

                <aside class="space-y-5">
                    <section
                        class="rounded-xl border border-[#e2e7df] bg-white p-5 shadow-[0_2px_8px_rgba(21,42,31,0.025)]"
                    >
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-semibold">
                                Evaluation design
                            </h2>
                            <span
                                class="rounded bg-[#f0f3ee] px-1.5 py-1 font-mono text-[10px] text-[#627067]"
                                >v1.0</span
                            >
                        </div>
                        <div class="mt-5 space-y-0">
                            <div class="flex gap-3 pb-4">
                                <span
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#eef4e7] text-xs font-bold text-[#577e3f]"
                                    >1</span
                                >
                                <div>
                                    <p class="text-xs font-semibold">
                                        Gold questions
                                    </p>
                                    <p
                                        class="mt-1 text-[11px] leading-4 text-[#849088]"
                                    >
                                        Five questions with human-labeled
                                        relevant chunk IDs.
                                    </p>
                                </div>
                            </div>
                            <div
                                class="ml-3.5 h-4 border-l border-dashed border-[#d3dbd0]"
                            ></div>
                            <div class="flex gap-3 py-3">
                                <span
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#eef4e7] text-xs font-bold text-[#577e3f]"
                                    >2</span
                                >
                                <div>
                                    <p class="text-xs font-semibold">
                                        TF-IDF retrieval
                                    </p>
                                    <p
                                        class="mt-1 text-[11px] leading-4 text-[#849088]"
                                    >
                                        Cosine-ranked FAQ chunks; stable and
                                        deterministic.
                                    </p>
                                </div>
                            </div>
                            <div
                                class="ml-3.5 h-4 border-l border-dashed border-[#d3dbd0]"
                            ></div>
                            <div class="flex gap-3 pt-3">
                                <span
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#eaf1ec] text-xs font-bold text-[#355c4d]"
                                    >3</span
                                >
                                <div>
                                    <p class="text-xs font-semibold">
                                        Recall@k
                                    </p>
                                    <p
                                        class="mt-1 text-[11px] leading-4 text-[#849088]"
                                    >
                                        Relevant IDs found in top-k ÷ all
                                        relevant IDs.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section
                        class="rounded-xl border border-[#e2e7df] bg-white p-5 shadow-[0_2px_8px_rgba(21,42,31,0.025)]"
                    >
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-semibold">
                                Corpus preview
                            </h2>
                            <span class="text-[11px] font-medium text-[#849088]"
                                >{{ chunks.length }} docs</span
                            >
                        </div>
                        <ul class="mt-3 divide-y divide-[#f0f2ef]">
                            <li
                                v-for="chunk in chunks"
                                :key="chunk.id"
                                class="flex items-center gap-2.5 py-2.5 first:pt-1 last:pb-0"
                            >
                                <span
                                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-[#f2f4f1] text-[#7b8780]"
                                >
                                    <svg
                                        viewBox="0 0 16 16"
                                        fill="none"
                                        class="h-3.5 w-3.5"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M4 2.5h5l3 3v8H4v-11Z"
                                            stroke="currentColor"
                                            stroke-width="1.2"
                                            stroke-linejoin="round"
                                        />
                                        <path
                                            d="M9 2.5v3h3M6 8h4M6 10.5h4"
                                            stroke="currentColor"
                                            stroke-width="1.1"
                                            stroke-linecap="round"
                                        />
                                    </svg>
                                </span>
                                <span class="min-w-0"
                                    ><span
                                        class="block truncate text-[11px] font-medium text-[#4c5951]"
                                        >{{ chunk.title }}</span
                                    ><span
                                        class="block truncate font-mono text-[9px] text-[#a0a9a2]"
                                        >{{ chunk.id }}</span
                                    ></span
                                >
                            </li>
                        </ul>
                    </section>

                    <p class="px-1 text-[11px] leading-5 text-[#8b958e]">
                        {{
                            llmEnabled
                                ? 'Gemini is used only to explain missed relevant chunks. Scoring remains deterministic.'
                                : 'Runs entirely without an API key. Miss notes use a deterministic fallback.'
                        }}
                    </p>
                </aside>
            </section>

            <footer
                class="mt-10 flex flex-col justify-between gap-2 border-t border-[#e2e7df] py-5 text-[11px] text-[#929b94] sm:flex-row sm:items-center"
            >
                <span
                    >RAG Eval Desk <span class="mx-1.5">·</span> a deterministic
                    retrieval quality demo</span
                >
                <span
                    >Laravel 13 <span class="mx-1">·</span> Vue 3
                    <span class="mx-1">·</span> TF-IDF cosine</span
                >
            </footer>
        </main>
    </div>
</template>
