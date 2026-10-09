@php
    $isEdit = $exam->exists;
@endphp

<div class="space-y-6">
    <div>
        <x-input-label for="title" :value="__('Título del examen')" />
        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                      :value="old('title', $exam->title)" required autofocus />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="description" :value="__('Descripción (opcional)')" />
        <textarea id="description" name="description" rows="3"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $exam->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <x-input-label for="duration_minutes" :value="__('Duración (minutos)')" />
            <x-text-input id="duration_minutes" name="duration_minutes" type="number" min="1" max="600"
                          class="mt-1 block w-full" :value="old('duration_minutes', $exam->duration_minutes ?? 25)" required />
            <x-input-error :messages="$errors->get('duration_minutes')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="questions_to_show" :value="__('Preguntas a mostrar')" />
            <x-text-input id="questions_to_show" name="questions_to_show" type="number" min="1" max="200"
                          class="mt-1 block w-full" :value="old('questions_to_show', $exam->questions_to_show ?? 5)" required />
            <p class="text-xs text-gray-500 mt-1">Se eligen al azar del total cargado.</p>
            <x-input-error :messages="$errors->get('questions_to_show')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="passing_score" :value="__('Aciertos para aprobar')" />
            <x-text-input id="passing_score" name="passing_score" type="number" min="0" max="200"
                          class="mt-1 block w-full" :value="old('passing_score', $exam->passing_score ?? 3)" required />
            <x-input-error :messages="$errors->get('passing_score')" class="mt-2" />
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <label class="inline-flex items-center">
            <input type="checkbox" name="shuffle_questions" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm"
                   @checked(old('shuffle_questions', $exam->shuffle_questions ?? true))>
            <span class="ms-2 text-sm text-gray-700">Mezclar preguntas</span>
        </label>
        <label class="inline-flex items-center">
            <input type="checkbox" name="shuffle_options" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm"
                   @checked(old('shuffle_options', $exam->shuffle_options ?? false))>
            <span class="ms-2 text-sm text-gray-700">Mezclar opciones</span>
        </label>
        <label class="inline-flex items-center">
            <input type="checkbox" name="show_correct_answers" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm"
                   @checked(old('show_correct_answers', $exam->show_correct_answers ?? false))>
            <span class="ms-2 text-sm text-gray-700">Mostrar respuestas correctas al final</span>
        </label>
        <label class="inline-flex items-center">
            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm"
                   @checked(old('is_active', $exam->is_active ?? true))>
            <span class="ms-2 text-sm text-gray-700">Examen activo (recibe respuestas)</span>
        </label>
    </div>
</div>
