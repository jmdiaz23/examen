<x-public-layout :title="$exam->title">
    <div class="max-w-2xl mx-auto px-4 py-6"
         x-data="examRunner(@js($config))"
         x-init="startTimer()">

        {{-- Barra superior: título, progreso y cronómetro --}}
        <div class="bg-white shadow-sm rounded-lg p-4 flex items-center justify-between sticky top-0 z-10">
            <div>
                <div class="text-xs text-gray-500">{{ $exam->title }}</div>
                <div class="text-sm font-semibold">
                    <span x-text="current + 1"></span> / <span x-text="total"></span>
                    <span class="text-gray-400 font-normal">· <span x-text="answeredCount"></span> respondidas</span>
                </div>
            </div>
            <div class="text-right">
                <div class="text-xs text-gray-500">Tiempo restante</div>
                <div class="text-lg font-bold tabular-nums"
                     :class="remaining <= 60 ? 'text-red-600' : 'text-gray-900'"
                     x-text="formatTime()"></div>
            </div>
        </div>

        {{-- Pregunta actual --}}
        <div class="bg-white shadow-sm rounded-lg p-6 mt-4">
            <p class="font-semibold text-gray-900 mb-4" x-text="question.text"></p>

            <div class="space-y-2">
                <template x-for="option in question.options" :key="option.id">
                    <button type="button"
                            @click="selectOption(option.id)"
                            class="w-full text-left px-4 py-3 rounded-md border-2 transition"
                            :class="isSelected(option.id)
                                ? 'border-indigo-600 bg-indigo-50'
                                : 'border-gray-200 hover:border-gray-300'">
                        <span x-text="option.text"></span>
                    </button>
                </template>
            </div>

            <div class="mt-2 h-4 text-xs text-green-600" x-show="saved" x-cloak>Respuesta guardada ✓</div>
        </div>

        {{-- Navegación --}}
        <div class="mt-4 flex items-center justify-between gap-3">
            <button type="button" @click="prev()" x-show="current > 0"
                    class="px-4 py-2 rounded-md border border-gray-300 text-sm hover:bg-gray-50">
                ← Anterior
            </button>
            <span x-show="current === 0"></span>

            <button type="button" @click="next()" x-show="current < total - 1"
                    class="px-4 py-2 rounded-md bg-gray-800 text-white text-sm hover:bg-gray-700">
                Siguiente →
            </button>

            <button type="button" @click="finish()" x-show="current === total - 1"
                    class="px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                Finalizar y enviar
            </button>
        </div>

        {{-- Puntos de navegación --}}
        <div class="mt-4 flex flex-wrap gap-2 justify-center">
            <template x-for="(q, index) in questions" :key="q.id">
                <button type="button" @click="goTo(index)"
                        class="w-8 h-8 rounded-full text-xs font-medium border"
                        :class="{
                            'bg-indigo-600 text-white border-indigo-600': current === index,
                            'bg-green-100 border-green-300 text-green-800': current !== index && answers[q.id],
                            'bg-white border-gray-300 text-gray-600': current !== index && !answers[q.id],
                        }"
                        x-text="index + 1"></button>
            </template>
        </div>

        {{-- Form de envío real --}}
        <form id="exam-submit-form" method="POST" action="{{ route('public.exam.submit', [$exam, $attempt]) }}" class="hidden">
            @csrf
        </form>
    </div>
</x-public-layout>
