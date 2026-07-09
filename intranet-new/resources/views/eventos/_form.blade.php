@php $evento = $evento ?? null; @endphp
<div>
    <label class="block text-sm font-medium">Título</label>
    <input type="text" name="title" value="{{ old('title', $evento->title ?? '') }}" class="mt-1 block w-full border-gray-300 rounded">
    @error('title') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
</div>
<div>
    <label class="block text-sm font-medium">Local</label>
    <input type="text" name="local" value="{{ old('local', $evento->local ?? '') }}" class="mt-1 block w-full border-gray-300 rounded">
    @error('local') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
</div>
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium">Data início</label>
        <input type="date" name="dt_start" value="{{ old('dt_start', optional($evento?->dt_start)->format('Y-m-d')) }}" class="mt-1 block w-full border-gray-300 rounded">
        @error('dt_start') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium">Data fim (opcional)</label>
        <input type="date" name="dt_end" value="{{ old('dt_end', optional($evento?->dt_end)->format('Y-m-d')) }}" class="mt-1 block w-full border-gray-300 rounded">
        @error('dt_end') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>
</div>
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium">Hora início</label>
        <input type="time" name="tm_start" value="{{ old('tm_start', $evento->tm_start ?? '') }}" class="mt-1 block w-full border-gray-300 rounded">
    </div>
    <div>
        <label class="block text-sm font-medium">Hora fim</label>
        <input type="time" name="tm_end" value="{{ old('tm_end', $evento->tm_end ?? '') }}" class="mt-1 block w-full border-gray-300 rounded">
    </div>
</div>
<div>
    <label class="block text-sm font-medium">Informações</label>
    <textarea name="informacoes" rows="5" class="mt-1 block w-full border-gray-300 rounded">{{ old('informacoes', $evento->informacoes ?? '') }}</textarea>
</div>
