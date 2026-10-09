@php
    if (old('options')) {
        $initialOptions = collect(old('options'))->map(fn ($o) => ['text' => $o['text'] ?? ''])->values()->all();
    } elseif (isset($question) && $question->exists) {
        $initialOptions = $question->options->map(fn ($o) => ['text' => $o->text])->values()->all();
    } else {
        $initialOptions = [['text' => ''], ['text' => ''], ['text' => ''], ['text' => '']];
    }

    if (old('correct') !== null) {
        $initialCorrect = (int) old('correct');
    } elseif (isset($question) && $question->exists) {
        $initialCorrect = $question->options->search(fn ($o) => $o->is_correct);
        $initialCorrect = $initialCorrect === false ? 0 : $initialCorrect;
    } else {
        $initialCorrect = 0;
    }
@endphp

<div class="space-y-6" x-data="questionForm(@js($initialOptions), {{ $initialCorrect }})">
    <div>
        <x-input-label for="text" :value="__('Pregunta')" />
        <textarea id="text" name="text" rows="3" required
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('text', $question->text ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('text')" class="mt-2" />
    </div>

    <div>
        <x-input-label :value="__('Opciones (marca la respuesta correcta)')" />

        <template x-for="(option, index) in options" :key="index">
            <div class="flex items-center gap-3 mt-2">
                <label class="inline-flex items-center">
                    <input type="radio" name="correct" :value="index" x-model.number="correct"
                           class="border-gray-300 text-indigo-600 shadow-sm">
                </label>
                <input type="text" :name="`options[${index}][text]`" x-model="options[index].text"
                       placeholder="Escribe una opción" required
                       class="flex-1 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-sm">
                <button type="button" @click="removeOption(index)" x-show="options.length > 2"
                        class="text-red-500 hover:text-red-700 px-2" title="Quitar opción">&times;</button>
            </div>
        </template>

        <x-input-error :messages="$errors->get('options')" class="mt-2" />
        <x-input-error :messages="$errors->get('options.*.text')" class="mt-2" />
        <x-input-error :messages="$errors->get('correct')" class="mt-2" />

        <button type="button" @click="addOption()" x-show="options.length < 6"
                class="mt-3 text-sm text-indigo-600 hover:text-indigo-900 underline">
            + Añadir opción
        </button>
    </div>
</div>
