<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar examen') }}
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('results.index', $exam) }}"
                   class="text-sm text-indigo-600 hover:text-indigo-900 underline">Resultados</a>
                <a href="{{ route('exams.index') }}"
                   class="text-sm text-gray-600 hover:text-gray-900 underline">Volver</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-red-100 text-red-800 rounded-md text-sm">
                    Revisa los campos marcados en rojo.
                </div>
            @endif

            {{-- Enlace público --}}
            <div x-data="{ copied: false, link: '{{ url('/examen/' . $exam->uuid) }}' }"
                 class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-2">Enlace público del examen</h3>
                <p class="text-sm text-gray-500 mb-3">Comparte este enlace con los estudiantes. Al abrirlo se registran y presentan el examen.</p>
                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="text" readonly value="{{ url('/examen/' . $exam->uuid) }}"
                           class="flex-1 rounded-md border-gray-300 text-sm bg-gray-50">
                    <button type="button"
                            @click="navigator.clipboard.writeText(link); copied = true; setTimeout(() => copied = false, 1500)"
                            class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">
                        <span x-show="!copied">Copiar enlace</span>
                        <span x-show="copied" x-cloak>¡Copiado!</span>
                    </button>
                </div>
            </div>

            {{-- Configuración --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-4">Configuración</h3>
                <form method="POST" action="{{ route('exams.update', $exam) }}">
                    @csrf
                    @method('PUT')
                    @include('exams._form', ['exam' => $exam])

                    <div class="mt-8 flex justify-end">
                        <x-primary-button>{{ __('Guardar cambios') }}</x-primary-button>
                    </div>
                </form>
            </div>

            {{-- Preguntas --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-800">
                        Preguntas <span class="text-gray-400 font-normal">({{ $exam->questions->count() }})</span>
                    </h3>
                    <a href="{{ route('questions.create', $exam) }}"
                       class="px-3 py-2 bg-gray-800 text-white text-xs uppercase rounded-md hover:bg-gray-700">
                        + Agregar pregunta
                    </a>
                </div>

                @if ($exam->questions->isEmpty())
                    <p class="text-sm text-gray-500">No hay preguntas todavía.</p>
                @else
                    <ul class="space-y-4">
                        @foreach ($exam->questions as $question)
                            <li class="border border-gray-200 rounded-md p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <p class="font-medium text-gray-900">
                                        {{ $loop->iteration }}. {{ $question->text }}
                                    </p>
                                    <div class="shrink-0 flex items-center gap-3">
                                        <a href="{{ route('questions.edit', [$exam, $question]) }}"
                                           class="text-xs text-indigo-600 hover:text-indigo-900 underline">Editar</a>
                                        <form method="POST" action="{{ route('questions.destroy', [$exam, $question]) }}"
                                              onsubmit="return confirm('¿Eliminar esta pregunta?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 hover:text-red-900 underline">Eliminar</button>
                                        </form>
                                    </div>
                                </div>
                                <ul class="mt-3 space-y-1 text-sm">
                                    @foreach ($question->options as $option)
                                        <li class="flex items-center gap-2 {{ $option->is_correct ? 'text-green-700 font-medium' : 'text-gray-600' }}">
                                            <span class="inline-block w-3 text-center">{{ $option->is_correct ? '✓' : '·' }}</span>
                                            {{ $option->text }}
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
