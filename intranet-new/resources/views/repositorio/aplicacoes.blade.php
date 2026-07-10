<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Aplicações Office</h2>
    </x-slot>
    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <form method="POST" action="{{ route('onlyoffice.criar') }}" class="bg-white shadow rounded p-6 text-center space-y-3">
                @csrf
                <input type="hidden" name="tipo" value="docx">
                <div class="text-4xl">📄</div>
                <p class="font-semibold">Documento Word</p>
                <input type="text" name="titulo" placeholder="Nome do documento" class="block w-full border-gray-300 rounded text-sm">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded w-full">Criar novo</button>
            </form>

            <form method="POST" action="{{ route('onlyoffice.criar') }}" class="bg-white shadow rounded p-6 text-center space-y-3">
                @csrf
                <input type="hidden" name="tipo" value="xlsx">
                <div class="text-4xl">📊</div>
                <p class="font-semibold">Planilha Excel</p>
                <input type="text" name="titulo" placeholder="Nome da planilha" class="block w-full border-gray-300 rounded text-sm">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded w-full">Criar novo</button>
            </form>

            <form method="POST" action="{{ route('onlyoffice.criar') }}" class="bg-white shadow rounded p-6 text-center space-y-3">
                @csrf
                <input type="hidden" name="tipo" value="pptx">
                <div class="text-4xl">📽️</div>
                <p class="font-semibold">Apresentação PowerPoint</p>
                <input type="text" name="titulo" placeholder="Nome da apresentação" class="block w-full border-gray-300 rounded text-sm">
                <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded w-full">Criar novo</button>
            </form>
        </div>

        <h3 class="font-semibold text-lg mb-3">Meus documentos</h3>
        <div class="bg-white shadow rounded overflow-hidden">
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
                    @forelse($documentos as $doc)
                        <tr class="border-t">
                            <td class="p-3">{{ $doc->nome_original }}</td>
                            <td class="p-3 uppercase">{{ $doc->extensao }}</td>
                            <td class="p-3">{{ $doc->tamanhoFormatado() }}</td>
                            <td class="p-3 text-right">
                                <a href="{{ route('onlyoffice.editor', $doc) }}" class="text-green-700">Abrir no editor</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-3 text-gray-500">Nenhum documento ainda. Crie um acima!</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
