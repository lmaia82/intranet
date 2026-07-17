<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Saúde do Sistema</h2>
    </x-slot>
    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <div class="bg-white shadow rounded p-4">
            <h3 class="font-semibold text-lg mb-4">🔌 Status dos Serviços</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach($servicos as $servico)
                    <div class="flex justify-between items-center text-sm border rounded px-3 py-2" title="{{ $servico['detalhe'] }}">
                        <span class="text-gray-800">{{ $servico['nome'] }}</span>
                        @if($servico['disponivel'])
                            <span class="inline-block px-2 py-0.5 text-xs rounded bg-green-100 text-green-800">🟢 Disponível</span>
                        @else
                            <span class="inline-block px-2 py-0.5 text-xs rounded bg-red-100 text-red-800">🔴 Indisponível</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white shadow rounded p-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-lg">📄 OCR (paperless-ngx)</h3>
            </div>

            <div class="grid grid-cols-3 gap-4 mb-4">
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-600">{{ $ocrPorStatus['concluido'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500">Concluído</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-yellow-600">{{ $ocrPorStatus['pendente'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500">Processando</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-red-600">{{ $ocrPorStatus['falhou'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500">Falhou</p>
                </div>
            </div>

            @if($arquivosComFalhaOcr->isNotEmpty())
                <p class="text-sm font-medium text-gray-700 mb-2">Falhas recentes:</p>
                <div class="space-y-2">
                    @foreach($arquivosComFalhaOcr as $arquivo)
                        <div class="flex justify-between items-start text-sm border-b pb-2 last:border-0">
                            <div>
                                <a href="{{ route('repositorio.arquivos.editar', $arquivo) }}" class="text-blue-700 hover:underline">{{ $arquivo->nome_original }}</a>
                                <p class="text-xs text-gray-500">{{ $arquivo->ocr_erro }}</p>
                            </div>
                            <span class="text-xs text-gray-400 whitespace-nowrap ml-2">{{ $arquivo->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bg-white shadow rounded p-4">
            <h3 class="font-semibold text-lg mb-4">✉️ E-mails de Informativos (30 dias)</h3>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-600">{{ $enviosEmail['sucesso'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500">Enviados com sucesso</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-red-600">{{ $enviosEmail['falha'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500">Falharam</p>
                </div>
            </div>

            @if($emailsComFalha->isNotEmpty())
                <p class="text-sm font-medium text-gray-700 mb-2">Falhas recentes:</p>
                <div class="space-y-2">
                    @foreach($emailsComFalha as $envio)
                        <div class="text-sm border-b pb-2 last:border-0">
                            <p>
                                <span class="text-gray-800">{{ $envio->email }}</span>
                                @if($envio->informativo)
                                    <span class="text-gray-500"> — {{ $envio->informativo->title }}</span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-500">{{ $envio->erro }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500">Nenhuma falha de envio nos últimos 30 dias.</p>
            @endif
        </div>

        <div class="bg-white shadow rounded p-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-lg">💾 Setores perto da cota de armazenamento</h3>
                <a href="{{ route('admin.armazenamento') }}" class="text-sm text-blue-600">Ver tudo &rarr;</a>
            </div>
            @forelse($setoresProximosDaCota as $sector)
                <div class="flex justify-between items-center text-sm border-b py-2 last:border-0">
                    <span class="text-gray-800">{{ $sector->sigla }}</span>
                    <span class="inline-block px-2 py-0.5 text-xs rounded {{ $sector->percentualUso() >= 90 ? 'bg-red-100 text-red-800' : 'bg-orange-100 text-orange-800' }}">
                        {{ $sector->percentualUso() }}% ({{ $sector->usoFormatado() }} / {{ $sector->quotaFormatada() }})
                    </span>
                </div>
            @empty
                <p class="text-sm text-gray-500">Nenhum setor acima de 80% da cota.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
