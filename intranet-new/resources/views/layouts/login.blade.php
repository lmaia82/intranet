<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="relative min-h-screen">
            <div class="fixed inset-0 bg-cover bg-center" style="background-image: url('{{ asset('images/frente_cr.png') }}')"></div>

            <div class="relative min-h-screen flex flex-col">
                <header class="bg-gradient-to-b from-blue-300 to-white shadow">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
                        <a href="https://cetem.gov.br" target="_blank" rel="noopener" class="inline-flex items-center">
                            <img src="{{ asset('images/logo-cetem.png') }}" alt="CETEM - Centro de Tecnologia Mineral" class="h-8 w-auto">
                        </a>
                        <span class="inline-flex items-center">
                            <img src="{{ asset('images/logo-intra.png') }}" alt="Intranet" class="h-8 w-auto">
                        </span>
                    </div>
                </header>

                <main class="flex-1 flex flex-col items-center justify-center px-4 py-12">
                    <div class="w-full sm:max-w-md px-6 py-8 bg-white shadow-md overflow-hidden sm:rounded-lg">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
