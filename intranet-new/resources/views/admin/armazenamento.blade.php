<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Armazenamento por Setor</h2>
    </x-slot>
    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-4">
            <p class="text-sm text-gray-500">Consumo de armazenamento do Repositório de Arquivos, por setor.</p>
            <a href="{{ route('admin.setores') }}" class="text-blue-600 text-sm">Editar cotas &rarr;</a>
        </div>

        <div class="bg-white shadow rounded p-4 mb-6 text-center">
            <p class="text-3xl font-bold text-blue-600">{{ \App\Models\Sector::formatarBytes($setores->sum(fn($s) => $s->usoBytes())) }}</p>
            <p class="text-sm text-gray-500">Uso total (setores com arquivos vinculados)</p>
        </div>

        <div class="space-y-4">
            @forelse($setores as $setor)
                @php $percentual = $setor->percentualUso(); @endphp
                <div class="bg-white shadow rounded p-4">
                    <div class="flex justify-between items-baseline mb-2">
                        <p class="font-semibold">{{ $setor->sigla }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $setor->usoFormatado() }} / {{ $setor->quotaFormatada() }}
                        </p>
                    </div>
                    @if($percentual !== null)
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="h-2.5 rounded-full {{ $percentual >= 90 ? 'bg-red-600' : ($percentual >= 70 ? 'bg-orange-500' : 'bg-blue-600') }}"
                                 style="width: {{ $percentual }}%"></div>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">{{ $percentual }}% da cota utilizada</p>
                    @else
                        <p class="text-xs text-gray-400">Sem cota definida (uso ilimitado)</p>
                    @endif
                </div>
            @empty
                <p class="text-gray-500">Nenhum setor cadastrado.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
