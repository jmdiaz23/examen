@php
    $answers = $attempt->answers->keyBy('question_id');
    $questionIds = $attempt->question_ids ?? [];
    $questions = \App\Models\Question::with('options')->whereIn('id', $questionIds)->get()->keyBy('id');
    $ordered = collect($questionIds)->map(fn ($id) => $questions->get($id))->filter();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalle del intento') }}
            </h2>
            <a href="{{ route('results.index', $exam) }}" class="text-sm text-gray-600 hover:text-gray-900 underline">Volver a resultados</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Nombre</dt><dd class="font-medium text-gray-900">{{ $attempt->full_name }}</dd></div>
                    <div><dt class="text-gray-500">Semestre</dt><dd class="font-medium text-gray-900">{{ $attempt->semester }}</dd></div>
                    <div><dt class="text-gray-500">Correo</dt><dd class="font-medium text-gray-900">{{ $attempt->email }}</dd></div>
                    <div>
                        <dt class="text-gray-500">Nota</dt>
                        <dd class="font-bold {{ $attempt->score >= $exam->passing_score ? 'text-green-600' : 'text-red-600' }}">
                            {{ $attempt->score }} / {{ $attempt->total_questions }}
                        </dd>
                    </div>
                    <div><dt class="text-gray-500">Inició</dt><dd class="text-gray-900">{{ $attempt->started_at?->format('d/m/Y H:i:s') }}</dd></div>
                    <div><dt class="text-gray-500">Envió</dt><dd class="text-gray-900">{{ $attempt->submitted_at?->format('d/m/Y H:i:s') }}</dd></div>
                </dl>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-4">Preguntas</h3>
                <ul class="space-y-5">
                    @foreach ($ordered as $question)
                        @php $answer = $answers->get($question->id); @endphp
                        <li class="border border-gray-200 rounded-md p-4">
                            <p class="font-medium text-gray-900 mb-2">{{ $loop->iteration }}. {{ $question->text }}</p>
                            <ul class="space-y-1 text-sm">
                                @foreach ($question->options as $option)
                                    @php $chosen = $answer && $answer->option_id === $option->id; @endphp
                                    <li class="flex items-center gap-2
                                        {{ $option->is_correct ? 'text-green-700 font-medium' : ($chosen ? 'text-red-600' : 'text-gray-600') }}">
                                        <span class="inline-block w-4 text-center">
                                            @if ($option->is_correct) ✓
                                            @elseif ($chosen) ✗
                                            @else ·
                                            @endif
                                        </span>
                                        {{ $option->text }}
                                        @if ($chosen)
                                            <span class="text-xs text-gray-400">(respuesta del estudiante)</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
