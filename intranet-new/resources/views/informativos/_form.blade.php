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
    <select name="sector_id" class="mt-1 block w-full border-gray-300 rounded">
        <option value="">(Geral, sem setor)</option>
        @foreach($sectors as $sector)
            <option value="{{ $sector->id }}" @selected(old('sector_id', $informativo->sector_id ?? null) == $sector->id)>{{ $sector->sigla }}</option>
        @endforeach
    </select>
</div>
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
