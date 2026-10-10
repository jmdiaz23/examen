<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nuevo usuario</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="name" value="Nombre" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                      :value="old('name')" required autofocus autocomplete="off" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Correo" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                      :value="old('email')" required autocomplete="off" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password" value="Contraseña" />
                        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                                      required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        <p class="mt-1 text-xs text-gray-500">Mínimo 8 caracteres.</p>
                    </div>

                    <div>
                        <x-input-label for="password_confirmation" value="Confirmar contraseña" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                                      class="mt-1 block w-full" required autocomplete="new-password" />
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('users.index') }}"
                           class="text-sm text-gray-600 hover:text-gray-900 underline">Cancelar</a>
                        <x-primary-button>Crear usuario</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>