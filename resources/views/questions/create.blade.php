<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nueva pregunta') }} — {{ $exam->title }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('questions.store', $exam) }}">
                    @csrf
                    @include('questions._form', ['question' => new \App\Models\Question()])

                    <div class="mt-8 flex items-center justify-end gap-3">
                        <a href="{{ route('exams.edit', $exam) }}" class="text-sm text-gray-600 hover:text-gray-900 underline">Cancelar</a>
                        <x-primary-button>{{ __('Guardar pregunta') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
