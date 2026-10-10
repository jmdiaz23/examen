<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Usuarios</h2>
            <a href="{{ route('users.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                + Nuevo usuario
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
                    @if ($users->isEmpty())
                        <p class="text-gray-500">Aún no hay usuarios.</p>
                    @else
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="text-left text-xs uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="py-3 pr-4">Nombre</th>
                                    <th class="py-3 px-4">Correo</th>
                                    <th class="py-3 px-4">Creado</th>
                                    <th class="py-3 pl-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($users as $user)
                                    <tr>
                                        <td class="py-3 pr-4 font-medium text-gray-900">
                                            {{ $user->name }}
                                            @if ($user->id === Auth::id())
                                                <span class="text-xs text-gray-400">(tú)</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4">{{ $user->email }}</td>
                                        <td class="py-3 px-4">{{ $user->created_at?->format('d/m/Y') }}</td>
                                        <td class="py-3 pl-4 text-right">
                                            @if ($user->id !== Auth::id())
                                                <form method="POST" action="{{ route('users.destroy', $user) }}"
                                                      onsubmit="return confirm('¿Eliminar a {{ $user->name }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:underline text-xs">Eliminar</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>