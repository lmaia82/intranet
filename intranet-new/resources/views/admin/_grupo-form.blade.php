@php $grupo = $grupo ?? null; @endphp
<div>
    <label class="block text-sm font-medium">Nome do grupo</label>
    <input type="text" name="name" value="{{ old('name', $grupo->name ?? '') }}" class="mt-1 block w-full border-gray-300 rounded">
    @error('name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
</div>

<div>
    <label class="block text-sm font-medium mb-2">Permissões</label>
    <p class="text-xs text-gray-500 mb-3">Marque "Ver" para permitir acesso à tela, e "Criar/editar" para permitir cadastrar, editar e remover registros nela.</p>
    @php $selecionadas = old('permissions', $grupo?->permissions->pluck('id')->all() ?? []); @endphp
    <div class="border rounded divide-y">
        @foreach($permissoes as $tela => $perms)
            <div class="p-3 flex items-center justify-between">
                <span class="font-medium capitalize">{{ $tela }}</span>
                <div class="flex gap-4">
                    @foreach($perms as $permissao)
                        <label class="flex items-center gap-1 text-sm">
                            <input type="checkbox" name="permissions[]" value="{{ $permissao->id }}" @checked(in_array($permissao->id, $selecionadas))>
                            {{ str_contains($permissao->key, '.ver') ? 'Ver' : 'Criar/editar' }}
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
