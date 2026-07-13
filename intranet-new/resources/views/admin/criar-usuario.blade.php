<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Novo usuário</h2>
    </x-slot>
    <div class="py-6 max-w-xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('admin.usuarios.store') }}" class="bg-white shadow rounded p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium">Nome</label>
                <input type="text" name="name" value="{{ old('name') }}" class="mt-1 block w-full border-gray-300 rounded">
                @error('name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium">E-mail</label>
                <input type="email" name="email" value="{{ old('email') }}" class="mt-1 block w-full border-gray-300 rounded">
                @error('email') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Senha</label>
                <input type="password" name="password" class="mt-1 block w-full border-gray-300 rounded">
                @error('password') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Confirmar senha</label>
                <input type="password" name="password_confirmation" class="mt-1 block w-full border-gray-300 rounded">
            </div>
            <div>
                <label class="block text-sm font-medium">Setor</label>
                <select name="sector_id" class="mt-1 block w-full border-gray-300 rounded">
                    <option value="">(nenhum)</option>
                    @foreach($setores as $setor)
                        <option value="{{ $setor->id }}" @selected(old('sector_id') == $setor->id)>{{ $setor->name }}</option>
                    @endforeach
                </select>
                @error('sector_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_admin" value="1" id="is_admin">
                <label for="is_admin" class="text-sm">Administrador</label>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Criar usuário</button>
        </form>
    </div>
</x-app-layout>
