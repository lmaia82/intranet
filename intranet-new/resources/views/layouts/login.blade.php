<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Intranet') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

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
                <header class="bg-gradient-to-b from-[#B9DBF7] to-white shadow">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
                        <a href="https://cetem.gov.br" target="_blank" rel="noopener" class="inline-flex items-center">
                            <img src="{{ asset('images/logo-cetem.png') }}" alt="CETEM - Centro de Tecnologia Mineral" class="h-8 w-auto">
                        </a>
                        <span class="inline-flex items-center">
                            <img src="{{ asset('images/logo-intra.png') }}" alt="Intranet" class="h-8 w-auto">
                        </span>
                    </div>
                </header>

                @if($asModal)
                    <div class="w-full h-14 bg-[#166F9E] flex items-center">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full flex justify-end">
                            <button
                                type="button"
                                x-data
                                x-on:click="$dispatch('open-modal', 'login')"
                                class="px-4 py-1.5 bg-white text-[#166F9E] text-sm font-semibold rounded hover:bg-blue-50 transition"
                            >
                                Entrar
                            </button>
                        </div>
                    </div>

                    <main class="flex-1 py-8">
                        {{ $preview ?? '' }}
                    </main>

                    <x-modal name="login" :show="$errors->any() || session('status')" maxWidth="md">
                        <div class="px-6 py-8">
                            <p class="text-center text-gray-700 font-medium mb-4">Entre para acessar as funcionalidades</p>
                            {{ $slot }}
                        </div>
                    </x-modal>
                @else
                    <div class="w-full h-[0.7cm] bg-[#166F9E]"></div>

                    <main class="flex-1 flex flex-col items-center justify-center px-4 py-12">
                        <div class="w-full sm:max-w-md px-6 py-8 bg-white shadow-md overflow-hidden sm:rounded-lg">
                            {{ $slot }}
                        </div>
                    </main>
                @endif
            </div>
        </div>
    </body>
</html>
