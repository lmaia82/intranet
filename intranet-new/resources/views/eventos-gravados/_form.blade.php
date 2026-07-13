@php $eventoGravado = $eventoGravado ?? null; @endphp
<div>
    <label class="block text-sm font-medium">Título</label>
    <input type="text" name="titulo" value="{{ old('titulo', $eventoGravado->titulo ?? '') }}" class="mt-1 block w-full border-gray-300 rounded">
    @error('titulo') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
</div>
<div>
    <label class="block text-sm font-medium">Data</label>
    <input type="date" name="data" value="{{ old('data', optional($eventoGravado?->data)->format('Y-m-d')) }}" class="mt-1 block w-full border-gray-300 rounded">
    @error('data') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
</div>
<div>
    <label class="block text-sm font-medium">Link do YouTube</label>
    <input type="url" name="youtube_url" value="{{ old('youtube_url', $eventoGravado->youtube_url ?? '') }}" placeholder="https://www.youtube.com/watch?v=..." class="mt-1 block w-full border-gray-300 rounded">
    @error('youtube_url') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
</div>
