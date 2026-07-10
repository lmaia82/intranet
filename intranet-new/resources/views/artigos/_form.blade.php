@php $artigo = $artigo ?? null; @endphp
<div>
    <label class="block text-sm font-medium">Título</label>
    <input type="text" name="titulo" value="{{ old('titulo', $artigo->titulo ?? '') }}" class="mt-1 block w-full border-gray-300 rounded">
    @error('titulo') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
</div>
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium">Ano</label>
        <input type="number" name="ano" value="{{ old('ano', $artigo->ano ?? date('Y')) }}" class="mt-1 block w-full border-gray-300 rounded">
        @error('ano') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium">Autores</label>
        <input type="text" name="autores" value="{{ old('autores', $artigo->autores ?? '') }}" placeholder="Separe por vírgula" class="mt-1 block w-full border-gray-300 rounded">
        @error('autores') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>
</div>
<div>
    <label class="block text-sm font-medium">Palavras-chave</label>
    <input type="text" name="palavras_chave" value="{{ old('palavras_chave', $artigo->palavras_chave ?? '') }}" placeholder="Separe por vírgula" class="mt-1 block w-full border-gray-300 rounded">
</div>
<div>
    <label class="block text-sm font-medium">Arquivo PDF {{ $artigo ? '(deixe em branco para manter o atual)' : '' }}</label>
    <input type="file" name="arquivo" accept="application/pdf" class="mt-1 block w-full">
    @error('arquivo') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    @if(!empty($artigo?->arquivo))
        <a href="{{ Storage::url($artigo->arquivo) }}" target="_blank" class="text-blue-600 text-sm">Ver arquivo atual</a>
    @endif
</div>
