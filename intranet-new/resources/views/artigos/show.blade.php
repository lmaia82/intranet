<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $artigo->titulo }}</h2>
    </x-slot>
    <div class="py-6 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded p-6">
            <p class="text-sm text-gray-500 mb-2">Ano: {{ $artigo->ano }}</p>
            <p class="text-sm text-gray-500 mb-2">Autores: {{ $artigo->autores }}</p>
            @if($artigo->palavras_chave)
                <p class="text-sm text-gray-500 mb-4">Palavras-chave: {{ $artigo->palavras_chave }}</p>
            @endif
            <a href="{{ Storage::url($artigo->arquivo) }}" target="_blank" class="inline-block px-4 py-2 bg-blue-600 text-white rounded">Baixar PDF</a>
            <a href="{{ route('artigos.index') }}" class="inline-block mt-6 ml-2 text-blue-600">&larr; Voltar</a>
        </div>
    </div>
</x-app-layout>
