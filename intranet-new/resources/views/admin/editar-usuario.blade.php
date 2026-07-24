<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar usuário</h2>
    </x-slot>
    <div class="py-6 max-w-xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('admin.usuarios.update', $usuario) }}" class="bg-white shadow rounded p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium">Nome</label>
                <input type="text" name="name" value="{{ old('name', $usuario->name) }}" class="mt-1 block w-full border-gray-300 rounded">
                @error('name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium">E-mail</label>
                <input type="email" name="email" value="{{ old('email', $usuario->email) }}" class="mt-1 block w-full border-gray-300 rounded">
                @error('email') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Cargo</label>
                <input type="text" name="cargo" value="{{ old('cargo', $usuario->cargo) }}" class="mt-1 block w-full border-gray-300 rounded">
                @error('cargo') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
            @if($usuario->autenticadoViaAd())
                <p class="text-sm text-gray-500">
                    A senha deste usuário é validada no Active Directory e não pode ser alterada pela intranet.
                </p>
            @else
                <div>
                    <label class="block text-sm font-medium">Nova senha</label>
                    <input type="password" name="password" class="mt-1 block w-full border-gray-300 rounded" placeholder="Deixe em branco para manter a senha atual">
                    @error('password') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium">Confirmar nova senha</label>
                    <input type="password" name="password_confirmation" class="mt-1 block w-full border-gray-300 rounded">
                </div>
            @endif
            <div class="flex items-center gap-3">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Salvar</button>
                <a href="{{ route('admin.usuarios') }}" class="text-gray-600">Cancelar</a>
            </div>
        </form>
    </div>
</x-app-layout>
