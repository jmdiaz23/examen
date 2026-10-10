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

        @if ($errors->any())
            <div class="mt-4 p-3 bg-red-100 text-red-800 rounded-md text-sm">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

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
                    :disabled="! isAnswered(question.id)"
                    class="px-4 py-2 rounded-md text-white text-sm disabled:opacity-40 disabled:cursor-not-allowed"
                    :class="isAnswered(question.id) ? 'bg-gray-800 hover:bg-gray-700' : 'bg-gray-400'">
                Siguiente →
            </button>

            <button type="button" @click="finish()" x-show="current === total - 1"
                    :disabled="! allAnswered"
                    class="px-4 py-2 rounded-md text-white text-sm font-semibold disabled:opacity-40 disabled:cursor-not-allowed"
                    :class="allAnswered ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-indigo-300'">
                Finalizar y enviar
            </button>
        </div>

        <p class="mt-2 text-xs text-center text-gray-500" x-show="! allAnswered" x-cloak>
            Responde todas las preguntas para poder enviar el examen.
        </p>

        {{-- Puntos de navegación --}}
        <div class="mt-4 flex flex-wrap gap-2 justify-center">
            <template x-for="(q, index) in questions" :key="q.id">
                <button type="button" @click="goTo(index)"
                        :disabled="! isAnswered(q.id) && current !== index"
                        class="w-8 h-8 rounded-full text-xs font-medium border disabled:opacity-40 disabled:cursor-not-allowed"
                        :class="{
                            'bg-indigo-600 text-white border-indigo-600': current === index,
                            'bg-green-100 border-green-300 text-green-800': current !== index && answers[q.id],
                            'bg-white border-gray-300 text-gray-600': current !== index && !answers[q.id],
                        }"
                        x-text="index + 1"></button>
            </template>
        </div>

        {{-- Modal de aviso / confirmación --}}
        <div x-show="modal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-transition.opacity>
            <div class="absolute inset-0 bg-gray-900/60" @click="closeModal()"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6"
                 x-show="modal" x-transition>
                <div class="flex items-start gap-3">
                    <div class="shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                         :class="{
                             'bg-amber-100': modalType === 'missing',
                             'bg-indigo-100': modalType === 'confirm',
                             'bg-emerald-100': modalType === 'sending',
                         }">
                        <svg x-show="modalType === 'missing'" class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg x-show="modalType === 'confirm'" class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg x-show="modalType === 'sending'" class="w-5 h-5 text-emerald-600 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-lg font-semibold text-gray-900" x-text="modalTitle"></h3>
                        <p class="mt-1 text-sm text-gray-600" x-text="modalMessage"></p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="closeModal()"
                            x-show="modalType === 'confirm'"
                            class="px-4 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="button" @click="modalConfirm()"
                            :disabled="modalType === 'sending'"
                            class="px-4 py-2 rounded-md text-white text-sm font-semibold disabled:opacity-60 disabled:cursor-not-allowed"
                            :class="{
                                'bg-indigo-600 hover:bg-indigo-700': modalType === 'confirm',
                                'bg-gray-400': modalType === 'missing',
                                'bg-emerald-600': modalType === 'sending',
                            }"
                            x-text="modalConfirmText"></button>
                </div>
            </div>
        </div>

        {{-- Form de envío real --}}
        <form id="exam-submit-form" method="POST" action="{{ route('public.exam.submit', [$exam, $attempt]) }}" class="hidden">
            @csrf
            <input type="hidden" id="exam-autosubmit" name="auto" value="0">
        </form>
    </div>
</x-public-layout>
