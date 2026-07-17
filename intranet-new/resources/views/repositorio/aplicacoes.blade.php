<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Aplicações Office</h2>
    </x-slot>
    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <form method="POST" action="{{ route('onlyoffice.criar') }}" target="_blank" class="bg-white shadow rounded p-6 text-center space-y-3">
                @csrf
                <input type="hidden" name="tipo" value="docx">
                <div class="text-4xl">📄</div>
                <p class="font-semibold">Documento Word</p>
                <input type="text" name="titulo" placeholder="Nome do documento" class="block w-full border-gray-300 rounded text-sm">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded w-full">Criar novo</button>
            </form>

            <form method="POST" action="{{ route('onlyoffice.criar') }}" target="_blank" class="bg-white shadow rounded p-6 text-center space-y-3">
                @csrf
                <input type="hidden" name="tipo" value="xlsx">
                <div class="text-4xl">📊</div>
                <p class="font-semibold">Planilha Excel</p>
                <input type="text" name="titulo" placeholder="Nome da planilha" class="block w-full border-gray-300 rounded text-sm">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded w-full">Criar novo</button>
            </form>

            <form method="POST" action="{{ route('onlyoffice.criar') }}" target="_blank" class="bg-white shadow rounded p-6 text-center space-y-3">
                @csrf
                <input type="hidden" name="tipo" value="pptx">
                <div class="text-4xl">📽️</div>
                <p class="font-semibold">Apresentação PowerPoint</p>
                <input type="text" name="titulo" placeholder="Nome da apresentação" class="block w-full border-gray-300 rounded text-sm">
                <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded w-full">Criar novo</button>
            </form>

            <div class="bg-white shadow rounded p-6 text-center space-y-3 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="text-4xl">📕</div>
                    <p class="font-semibold">Stirling PDF</p>
                    <p class="text-xs text-gray-500">Mesclar, dividir, comprimir, assinar, converter e outras ferramentas de PDF.</p>
                </div>
                <a href="{{ config('services.stirling_pdf.url') }}" target="_blank" rel="noopener" class="block px-4 py-2 bg-red-600 text-white rounded w-full">Abrir Stirling PDF</a>
            </div>
        </div>

        <h3 class="font-semibold text-lg mb-3">Meus documentos</h3>
        @php
            $documentosIniciais = $documentos->map(fn ($d) => [
                'id' => $d->id,
                'nome_original' => $d->nome_original,
                'extensao' => $d->extensao,
                'tamanho_formatado' => $d->tamanhoFormatado(),
                'editor_url' => route('onlyoffice.editor', $d),
            ]);
        @endphp
        <div class="bg-white shadow rounded overflow-hidden"
            x-data="{
                documentos: @json($documentosIniciais),
                carregar() {
                    fetch('{{ route('onlyoffice.aplicacoes.documentos') }}')
                        .then(r => r.json())
                        .then(dados => { this.documentos = dados; })
                        .catch(() => {});
                }
            }"
            x-init="setInterval(() => carregar(), 5000)"
        >
            <table class="w-full text-left">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3">Nome</th>
                        <th class="p-3">Tipo</th>
                        <th class="p-3">Tamanho</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="documentos.length === 0">
                        <tr><td colspan="4" class="p-3 text-gray-500">Nenhum documento ainda. Crie um acima!</td></tr>
                    </template>
                    <template x-for="doc in documentos" :key="doc.id">
                        <tr class="border-t">
                            <td class="p-3" x-text="doc.nome_original"></td>
                            <td class="p-3 uppercase" x-text="doc.extensao"></td>
                            <td class="p-3" x-text="doc.tamanho_formatado"></td>
                            <td class="p-3 text-right">
                                <a :href="doc.editor_url" target="_blank" rel="noopener" class="text-green-700">Abrir no editor</a>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
