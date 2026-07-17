<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Engajamento</h2>
    </x-slot>
    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white shadow rounded p-4 text-center">
                <p class="text-3xl font-bold text-blue-600">{{ $usuariosAtivosHoje }}</p>
                <p class="text-sm text-gray-500">Usuários ativos hoje</p>
            </div>
            <div class="bg-white shadow rounded p-4 text-center">
                <p class="text-3xl font-bold text-blue-600">{{ $usuariosAtivos7d }}</p>
                <p class="text-sm text-gray-500">Usuários ativos (7 dias)</p>
            </div>
            <div class="bg-white shadow rounded p-4 text-center">
                <p class="text-3xl font-bold text-blue-600">{{ $usuariosAtivos30d }}</p>
                <p class="text-sm text-gray-500">Usuários ativos (30 dias)</p>
            </div>
        </div>

        <div class="bg-white shadow rounded p-4">
            <h3 class="font-semibold text-lg mb-4">Usuários ativos por dia (últimos 14 dias)</h3>
            <div class="space-y-2">
                @foreach($dias as $dia)
                    <div class="flex items-center gap-3 text-sm">
                        <span class="w-12 shrink-0 text-gray-500">{{ $dia['data']->format('d/m') }}</span>
                        <div class="flex-1 bg-gray-100 rounded-full h-3">
                            <div
                                class="bg-blue-600 h-3 rounded-full"
                                style="width: {{ $dia['usuarios'] > 0 ? max(4, round($dia['usuarios'] / $maxUsuariosDia * 100)) : 0 }}%"
                                title="{{ $dia['usuarios'] }} usuário(s) ativo(s), {{ $dia['acessos'] }} acesso(s) em {{ $dia['data']->format('d/m/Y') }}"
                            ></div>
                        </div>
                        <span class="w-8 shrink-0 text-right font-medium text-gray-700">{{ $dia['usuarios'] }}</span>
                    </div>
                @endforeach
            </div>
            <p class="text-xs text-gray-400 mt-3">Número de usuários distintos que acessaram algum módulo em cada dia.</p>
        </div>

        <div class="bg-white shadow rounded p-4">
            <h3 class="font-semibold text-lg mb-4">Acessos por módulo (últimos 30 dias)</h3>
            @forelse($acessosPorModulo as $registro)
                <div class="flex items-center gap-3 text-sm mb-2">
                    <span class="w-28 shrink-0 text-gray-700">{{ $nomesModulos[$registro->modulo] ?? $registro->modulo }}</span>
                    <div class="flex-1 bg-gray-100 rounded-full h-3">
                        <div class="bg-blue-600 h-3 rounded-full" style="width: {{ max(4, round($registro->total / $maxAcessosModulo * 100)) }}%"></div>
                    </div>
                    <span class="w-12 shrink-0 text-right font-medium text-gray-700">{{ $registro->total }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">Nenhum acesso registrado nos últimos 30 dias.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
