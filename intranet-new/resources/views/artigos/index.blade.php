<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Publicações</h2>
    </x-slot>
    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white shadow rounded p-8 text-center">
                <h3 class="text-2xl font-bold text-blue-700 mb-4">Bem-vindo ao Mineralis</h3>
                <p class="text-gray-600 leading-relaxed mb-6">
                    O repositório institucional do CETEM coleta, preserva e distribui
                    material digital, mantendo os princípios da segurança da informação.
                </p>
                <a href="https://mineralis.cetem.gov.br/buscar" target="_blank" rel="noopener"
                   class="inline-block px-6 py-3 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700">
                    Acessar o Mineralis
                </a>
            </div>

            <div class="bg-white shadow rounded p-8 text-center">
                <h3 class="text-2xl font-bold text-blue-700 mb-4">Bem-vindo ao Master</h3>
                <p class="text-gray-600 leading-relaxed mb-6">
                    A Biblioteca Digital Master disponibiliza a produção
                    técnico-científica do CETEM publicada por editores externos ao
                    Centro.
                </p>
                <a href="https://master.cetem.gov.br/" target="_blank" rel="noopener"
                   class="inline-block px-6 py-3 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700">
                    Acessar o Master
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
