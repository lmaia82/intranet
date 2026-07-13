<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reenviar e-mails — {{ $informativo->title }}</h2>
    </x-slot>
    <div class="py-6 max-w-2xl mx-auto sm:px-6 lg:px-8">
        @error('emails')
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">{{ $message }}</div>
        @enderror

        <div class="bg-white shadow rounded p-6 mb-4">
            <form method="GET" action="{{ route('informativos.reenviar.form', $informativo) }}" class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block text-sm font-medium">Carregar e-mails de um setor</label>
                    <select name="sector_id" class="mt-1 block w-full border-gray-300 rounded" onchange="this.form.submit()">
                        <option value="" @selected(!$sectorId)>Todos os usuários</option>
                        @foreach($sectors as $sector)
                            <option value="{{ $sector->id }}" @selected($sectorId == $sector->id)>{{ $sector->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
            <p class="text-xs text-gray-500 mt-2">
                Ao trocar o setor acima, a lista abaixo é recarregada com os e-mails
                sugeridos. Você pode editar livremente antes de enviar: adicionar
                e-mails que não sejam de usuários cadastrados, ou apagar linhas.
            </p>
        </div>

        <form method="POST" action="{{ route('informativos.reenviar', $informativo) }}" class="bg-white shadow rounded p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium">Destinatários (um e-mail por linha)</label>
                <textarea name="emails" rows="12" class="mt-1 block w-full border-gray-300 rounded font-mono text-sm">{{ old('emails', $emails->implode("\n")) }}</textarea>
                <p class="text-xs text-gray-500 mt-1">{{ $emails->count() }} e-mail(s) sugerido(s) para o setor selecionado.</p>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded">Enviar e-mails</button>
                <a href="{{ route('informativos.show', $informativo) }}" class="px-4 py-2 text-gray-600">Cancelar</a>
            </div>
        </form>
    </div>
</x-app-layout>
