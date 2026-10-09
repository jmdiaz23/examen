@props(['title' => null])

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? config('app.name', 'Exámenes') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body class="font-sans antialiased bg-gray-100 text-gray-900">
        <div class="min-h-screen flex flex-col">
            <header class="bg-white border-b border-gray-200">
                <div class="max-w-2xl mx-auto px-4 py-4">
                    <span class="font-bold text-lg">{{ config('app.name', 'Exámenes') }}</span>
                </div>
            </header>

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="py-6 text-center text-xs text-gray-400">
                {{ config('app.name', 'Exámenes') }} — {{ now()->year }}
            </footer>
        </div>
        @stack('scripts')
    </body>
</html>
