<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $informativo->title }}</h2>
    </x-slot>
    <div class="py-6 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded p-6">
            @if($informativo->image)
                <img src="{{ Storage::url($informativo->image) }}" class="w-full rounded mb-4">
            @endif
            <p class="text-sm text-gray-500 mb-4">
                {{ $informativo->sector->name ?? 'Geral' }} — {{ $informativo->published_at?->format('d/m/Y H:i') }}
                @if($informativo->is_private) <span class="text-red-600">(privado)</span> @endif
            </p>
            <div class="whitespace-pre-line">{{ $informativo->content }}</div>
            <a href="{{ route('informativos.index') }}" class="inline-block mt-6 text-blue-600">&larr; Voltar</a>
        </div>
    </div>
</x-app-layout>
