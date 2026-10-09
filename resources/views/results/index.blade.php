<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Resultados') }} — {{ $exam->title }}
            </h2>
            <a href="{{ route('exams.edit', $exam) }}" class="text-sm text-gray-600 hover:text-gray-900 underline">Volver al examen</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                @php
                    $cards = [
                        ['label' => 'Presentaron', 'value' => $stats['total'], 'color' => 'text-gray-900'],
                        ['label' => 'Promedio', 'value' => $stats['average'], 'color' => 'text-indigo-600'],
                        ['label' => 'Mayor nota', 'value' => $stats['max'], 'color' => 'text-green-600'],
                        ['label' => 'Menor nota', 'value' => $stats['min'], 'color' => 'text-red-600'],
                        ['label' => 'Aprobados', 'value' => $stats['passed'], 'color' => 'text-green-700'],
                    ];
                @endphp
                @foreach ($cards as $card)
                    <div class="bg-white shadow-sm sm:rounded-lg p-4 text-center">
                        <div class="text-xs uppercase text-gray-500">{{ $card['label'] }}</div>
                        <div class="text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @if ($attempts->isEmpty())
                    <p class="text-gray-500">Todavía nadie ha presentado este examen.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="text-left text-gray-500 uppercase text-xs tracking-wider">
                                    <th class="py-3 pr-4">Estudiante</th>
                                    <th class="py-3 px-4">Semestre</th>
                                    <th class="py-3 px-4">Correo</th>
                                    <th class="py-3 px-4 text-center">Nota</th>
                                    <th class="py-3 px-4 text-center">Resultado</th>
                                    <th class="py-3 px-4">Enviado</th>
                                    <th class="py-3 pl-4 text-right">Detalle</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($attempts as $attempt)
                                    <tr>
                                        <td class="py-3 pr-4 font-medium text-gray-900">{{ $attempt->full_name }}</td>
                                        <td class="py-3 px-4 text-gray-600">{{ $attempt->semester }}</td>
                                        <td class="py-3 px-4 text-gray-600">{{ $attempt->email }}</td>
                                        <td class="py-3 px-4 text-center font-semibold">{{ $attempt->score }} / {{ $attempt->total_questions }}</td>
                                        <td class="py-3 px-4 text-center">
                                            @if ($attempt->score >= $exam->passing_score)
                                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Aprobó</span>
                                            @else
                                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Reprobó</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-gray-500">
                                            {{ $attempt->submitted_at?->format('d/m/Y H:i') }}
                                            @if ($attempt->status === 'expired')
                                                <span class="text-xs text-amber-600">(tiempo agotado)</span>
                                            @endif
                                        </td>
                                        <td class="py-3 pl-4 text-right">
                                            <a href="{{ route('results.show', [$exam, $attempt]) }}"
                                               class="text-xs text-indigo-600 hover:text-indigo-900 underline">Ver</a>
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
</x-app-layout>
