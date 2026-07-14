<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tutoriais</h2>
    </x-slot>
    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
        @if(session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <div class="flex justify-between items-center mb-4">
            <h3 class="font-semibold text-lg">Vídeos tutoriais</h3>
            @if(auth()->user()->hasPermission('tutoriais.criar'))
                <div class="flex items-center gap-3">
                    <a href="{{ route('tutoriais.lote.form') }}" class="text-blue-600 text-sm">Cadastro em lote</a>
                    <a href="{{ route('tutoriais.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded text-sm">Novo tutorial</a>
                </div>
            @endif
        </div>

        <div class="bg-white shadow rounded overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3">Data</th>
                        <th class="p-3">Título</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tutoriais as $tutorial)
                        <tr class="border-t">
                            <td class="p-3">{{ $tutorial->data->format('d/m/Y') }}</td>
                            <td class="p-3">
                                <a href="{{ $tutorial->youtube_url }}" target="_blank" rel="noopener" class="text-blue-600 hover:underline">{{ $tutorial->titulo }}</a>
                            </td>
                            @if(auth()->user()->hasPermission('tutoriais.criar'))
                                <td class="p-3 text-right whitespace-nowrap">
                                    <a href="{{ route('tutoriais.edit', $tutorial) }}" class="text-blue-600">Editar</a>
                                    <form action="{{ route('tutoriais.destroy', $tutorial) }}" method="POST" class="inline" onsubmit="return confirm('Remover este tutorial?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 ml-2">Remover</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="3" class="p-3 text-gray-500">Nenhum tutorial cadastrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $tutoriais->links() }}</div>
    </div>
</x-app-layout>
