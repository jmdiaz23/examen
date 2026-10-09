<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Exámenes') }}
            </h2>
            <a href="{{ route('exams.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                + Nuevo examen
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="p-4 bg-green-100 text-green-800 rounded-md">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($exams->isEmpty())
                        <p class="text-gray-500">
                            Aún no hay exámenes. Crea el primero con el botón de arriba.
                        </p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead>
                                    <tr class="text-left text-gray-500 uppercase text-xs tracking-wider">
                                        <th class="py-3 pr-4">Título</th>
                                        <th class="py-3 px-4 text-center">Preguntas</th>
                                        <th class="py-3 px-4 text-center">A mostrar</th>
                                        <th class="py-3 px-4 text-center">Duración</th>
                                        <th class="py-3 px-4 text-center">Intentos</th>
                                        <th class="py-3 px-4 text-center">Estado</th>
                                        <th class="py-3 pl-4 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($exams as $exam)
                                        <tr x-data="{ copied: false, link: '{{ url('/examen/' . $exam->uuid) }}' }">
                                            <td class="py-4 pr-4">
                                                <div class="font-medium text-gray-900">{{ $exam->title }}</div>
                                                @if ($exam->description)
                                                    <div class="text-gray-500 text-xs mt-1">{{ \Illuminate\Support\Str::limit($exam->description, 80) }}</div>
                                                @endif
                                            </td>
                                            <td class="py-4 px-4 text-center">{{ $exam->questions_count }}</td>
                                            <td class="py-4 px-4 text-center">{{ $exam->questions_to_show }}</td>
                                            <td class="py-4 px-4 text-center">{{ $exam->duration_minutes }} min</td>
                                            <td class="py-4 px-4 text-center">{{ $exam->attempts_count }}</td>
                                            <td class="py-4 px-4 text-center">
                                                @if ($exam->is_active)
                                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Activo</span>
                                                @else
                                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-200 text-gray-600">Inactivo</span>
                                                @endif
                                            </td>
                                            <td class="py-4 pl-4 text-right whitespace-nowrap">
                                                <button type="button"
                                                        @click="navigator.clipboard.writeText(link); copied = true; setTimeout(() => copied = false, 1500)"
                                                        class="text-xs text-gray-600 hover:text-gray-900 underline">
                                                    <span x-show="!copied">Copiar enlace</span>
                                                    <span x-show="copied" x-cloak class="text-green-600">¡Copiado!</span>
                                                </button>
                                                <a href="{{ route('exams.edit', $exam) }}" class="ml-3 text-xs text-indigo-600 hover:text-indigo-900 underline">Editar</a>
                                                <a href="{{ route('results.index', $exam) }}" class="ml-3 text-xs text-indigo-600 hover:text-indigo-900 underline">Resultados</a>
                                                <form method="POST" action="{{ route('exams.destroy', $exam) }}" class="inline"
                                                      onsubmit="return confirm('¿Eliminar este examen y todos sus intentos?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="ml-3 text-xs text-red-600 hover:text-red-900 underline">Eliminar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
