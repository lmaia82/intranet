@php $informativo = $informativo ?? null; @endphp
<div>
    <label class="block text-sm font-medium">Título</label>
    <input type="text" name="title" value="{{ old('title', $informativo->title ?? '') }}" class="mt-1 block w-full border-gray-300 rounded">
    @error('title') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
</div>
<div>
    <label class="block text-sm font-medium">Conteúdo</label>
    <textarea name="content" rows="8" class="mt-1 block w-full border-gray-300 rounded">{{ old('content', $informativo->content ?? '') }}</textarea>
    @error('content') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
</div>
<div>
    <label class="block text-sm font-medium">Setor / Categoria</label>
    <select name="sector_id" id="sector_id" class="mt-1 block w-full border-gray-300 rounded" onchange="informativoAtualizarInfoServicos(this)">
        <option value="">(Geral, sem setor)</option>
        @foreach($sectors as $sector)
            <option value="{{ $sector->id }}"
                    data-servicos="{{ $sector->children->pluck('sigla')->implode(', ') }}"
                    @selected(old('sector_id', $informativo->sector_id ?? null) == $sector->id)>{{ $sector->sigla }}</option>
        @endforeach
    </select>
    <p id="informativo-info-servicos" class="text-sm text-gray-500 mt-1 hidden"></p>
</div>
<script>
    function informativoAtualizarInfoServicos(select) {
        const opcao = select.selectedOptions[0];
        const servicos = opcao ? opcao.dataset.servicos : '';
        const info = document.getElementById('informativo-info-servicos');

        if (servicos) {
            info.textContent = 'Esta coordenação inclui os serviços: ' + servicos + '. Eles também recebem o e-mail e, se o informativo for restrito, também têm acesso.';
            info.classList.remove('hidden');
        } else {
            info.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('sector_id');
        if (select) informativoAtualizarInfoServicos(select);
    });
</script>
<div>
    <label class="block text-sm font-medium">Imagem de capa (opcional)</label>
    <input type="file" name="image" accept="image/*" class="mt-1 block w-full">
    @error('image') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    @if($informativo?->imagemUrl())
        <img src="{{ $informativo->imagemUrl() }}" class="mt-2 h-24 rounded">
    @endif
</div>
<div class="flex items-center gap-2">
    <input type="checkbox" name="is_private" value="1" id="is_private" @checked(old('is_private', $informativo->is_private ?? false))>
    <label for="is_private" class="text-sm">Restrito ao setor (privado)</label>
</div>
