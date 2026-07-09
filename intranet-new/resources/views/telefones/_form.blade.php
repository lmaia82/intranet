@php $telefone = $telefone ?? null; @endphp
<div>
    <label class="block text-sm font-medium">Nome</label>
    <input type="text" name="nome" value="{{ old('nome', $telefone->nome ?? '') }}" class="mt-1 block w-full border-gray-300 rounded">
    @error('nome') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
</div>
<div>
    <label class="block text-sm font-medium">Ramal</label>
    <input type="text" name="telefone" value="{{ old('telefone', $telefone->telefone ?? '') }}" class="mt-1 block w-full border-gray-300 rounded">
    @error('telefone') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
</div>
<div>
    <label class="block text-sm font-medium">Setor</label>
    <select name="sector_id" class="mt-1 block w-full border-gray-300 rounded">
        <option value="">Selecione...</option>
        @foreach($sectors as $sector)
            <option value="{{ $sector->id }}" @selected(old('sector_id', $telefone->sector_id ?? null) == $sector->id)>{{ $sector->name }}</option>
        @endforeach
    </select>
    @error('sector_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
</div>
<div>
    <label class="block text-sm font-medium">Cargo</label>
    <input type="text" name="cargo" value="{{ old('cargo', $telefone->cargo ?? '') }}" class="mt-1 block w-full border-gray-300 rounded">
</div>
<div>
    <label class="block text-sm font-medium">E-mail</label>
    <input type="email" name="email" value="{{ old('email', $telefone->email ?? '') }}" class="mt-1 block w-full border-gray-300 rounded">
    @error('email') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
</div>
