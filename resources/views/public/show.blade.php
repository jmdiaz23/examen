<x-public-layout :title="$exam->title">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <div class="bg-white shadow-sm rounded-lg p-6">
            <h1 class="text-xl font-bold text-gray-900">{{ $exam->title }}</h1>
            @if ($exam->description)
                <p class="mt-2 text-sm text-gray-600">{{ $exam->description }}</p>
            @endif

            <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                <div class="bg-gray-50 rounded-md p-3">
                    <dt class="text-gray-500">Duración</dt>
                    <dd class="font-semibold">{{ $exam->duration_minutes }} minutos</dd>
                </div>
                <div class="bg-gray-50 rounded-md p-3">
                    <dt class="text-gray-500">Preguntas</dt>
                    <dd class="font-semibold">{{ $exam->questions_to_show }}</dd>
                </div>
            </dl>

            @if (! $exam->is_active)
                <div class="mt-5 p-4 bg-amber-100 text-amber-800 rounded-md text-sm">
                    Este examen no está disponible en este momento.
                </div>
            @else
                <p class="mt-5 text-sm text-gray-600">
                    Ingresa tus datos para comenzar. El cronómetro empieza al presionar <strong>Empezar examen</strong>
                    y no podrás volver a presentarlo.
                </p>

                @if ($errors->any())
                    <div class="mt-4 p-3 bg-red-100 text-red-800 rounded-md text-sm">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('public.exam.start', $exam) }}" class="mt-5 space-y-4">
                    @csrf

                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700">Nombre completo</label>
                        <input id="full_name" name="full_name" type="text" required value="{{ old('full_name') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                    </div>

                    <div>
                        <label for="semester" class="block text-sm font-medium text-gray-700">Semestre</label>
                        <input id="semester" name="semester" type="text" required value="{{ old('semester') }}"
                               placeholder="Ej: 5"
                               class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Correo electrónico</label>
                        <input id="email" name="email" type="email" required value="{{ old('email') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                    </div>

                    <button type="submit"
                            class="w-full py-3 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700">
                        Empezar examen
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-public-layout>
