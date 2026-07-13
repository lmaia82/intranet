<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $informativo->title }}</h2>
    </x-slot>
    <div class="py-6 max-w-2xl mx-auto sm:px-6 lg:px-8">
        @if(session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

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

        <div class="bg-white shadow rounded p-6 mt-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-gray-800">Envios por e-mail ({{ $informativo->envios->count() }})</h3>
                <a href="{{ route('informativos.reenviar.form', $informativo) }}" class="px-4 py-2 bg-orange-600 text-white rounded text-sm inline-block">Reenviar e-mails</a>
            </div>

            @if($informativo->envios->isEmpty())
                <p class="text-gray-500 text-sm">Nenhum e-mail enviado ainda.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="p-2">E-mail</th>
                                <th class="p-2">Enviado em</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($informativo->envios as $envio)
                                <tr class="border-t">
                                    <td class="p-2">{{ $envio->email }}</td>
                                    <td class="p-2">{{ $envio->enviado_em->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
