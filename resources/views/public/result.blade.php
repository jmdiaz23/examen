@php $passed = $attempt->passed(); @endphp

<x-public-layout :title="'Resultado — ' . $exam->title">
    <div class="max-w-2xl mx-auto px-4 py-10">
        <div class="bg-white shadow-sm rounded-lg p-8 text-center">
            <p class="text-sm text-gray-500">{{ $exam->title }}</p>
            <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ $attempt->full_name }}</h1>

            <div class="mt-8">
                <div class="text-sm uppercase tracking-wide text-gray-500">Obtuviste</div>
                <div class="text-5xl font-extrabold {{ $passed ? 'text-green-600' : 'text-red-600' }}">
                    {{ $attempt->score }} <span class="text-2xl text-gray-400">/ {{ $attempt->total_questions }}</span>
                </div>
            </div>

            <div class="mt-6">
                @if ($passed)
                    <div class="inline-block px-4 py-2 rounded-full bg-green-100 text-green-800 font-semibold">
                        ¡Aprobado! 🎉
                    </div>
                @else
                    <div class="inline-block px-4 py-2 rounded-full bg-red-100 text-red-800 font-semibold">
                        No aprobado
                    </div>
                    <p class="mt-2 text-sm text-gray-500">Necesitabas {{ $exam->passing_score }} aciertos.</p>
                @endif
            </div>

            @if ($attempt->status === 'expired')
                <p class="mt-4 text-xs text-amber-600">El tiempo se agotó y el examen se envió automáticamente.</p>
            @endif

            <p class="mt-8 text-xs text-gray-400">
                Enviado el {{ $attempt->submitted_at?->format('d/m/Y H:i') }}
            </p>
        </div>
    </div>

    @if ($exam->show_correct_answers && count($attempt->question_ids ?? []) > 0)
        @php
            $questionIds = $attempt->question_ids ?? [];
            $questions = \App\Models\Question::with('options')->whereIn('id', $questionIds)->get()->keyBy('id');
            $answers = $attempt->answers->keyBy('question_id');
            $ordered = collect($questionIds)->map(fn ($id) => $questions->get($id))->filter();
        @endphp
        <div class="max-w-2xl mx-auto px-4 pb-10">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h2 class="font-semibold text-gray-800 mb-4">Respuestas</h2>
                <ul class="space-y-4">
                    @foreach ($ordered as $question)
                        @php $answer = $answers->get($question->id); @endphp
                        <li class="border-b border-gray-100 pb-3">
                            <p class="font-medium text-sm text-gray-900">{{ $loop->iteration }}. {{ $question->text }}</p>
                            <p class="mt-1 text-sm">
                                Correcta:
                                <span class="text-green-700 font-medium">
                                    {{ $question->options->firstWhere('is_correct', true)?->text }}
                                </span>
                            </p>
                            <p class="text-sm text-gray-500">
                                Tu respuesta:
                                {{ $answer?->option?->text ?? 'Sin responder' }}
                            </p>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof confetti !== 'function') return;

                const passed = @json($passed);
                const colors = passed
                    ? ['#16a34a', '#22c55e', '#4ade80', '#facc15']
                    : ['#6366f1', '#818cf8', '#c7d2fe'];

                confetti({
                    particleCount: passed ? 160 : 70,
                    spread: passed ? 90 : 60,
                    origin: { y: 0.6 },
                    colors,
                });

                if (!passed) return;

                const end = Date.now() + 2000;
                (function frame() {
                    confetti({ particleCount: 3, angle: 60, spread: 55, origin: { x: 0 }, colors });
                    confetti({ particleCount: 3, angle: 120, spread: 55, origin: { x: 1 }, colors });
                    if (Date.now() < end) requestAnimationFrame(frame);
                })();
            });
        </script>
    @endpush
</x-public-layout>
