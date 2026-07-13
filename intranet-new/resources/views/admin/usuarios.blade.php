<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Usuários</h2>
    </x-slot>
    <div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8">
        @if(session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <div class="flex justify-end mb-4">
            <a href="{{ route('admin.usuarios.criar') }}" class="px-4 py-2 bg-blue-600 text-white rounded">Novo usuário</a>
        </div>
        <div class="bg-white shadow rounded overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3">Nome</th>
                        <th class="p-3">E-mail</th>
                        <th class="p-3">Setor</th>
                        <th class="p-3">Admin</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usuarios as $usuario)
                        <tr class="border-t">
                            <td class="p-3">{{ $usuario->name }}</td>
                            <td class="p-3">{{ $usuario->email }}</td>
                            <td class="p-3">
                                <form action="{{ route('admin.usuarios.setor', $usuario) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <select name="sector_id" class="border-gray-300 rounded text-sm" onchange="this.form.submit()">
                                        <option value="">(nenhum)</option>
                                        @foreach($setores as $setor)
                                            <option value="{{ $setor->id }}" @selected($usuario->sector_id == $setor->id)>{{ $setor->name }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="p-3">{{ $usuario->is_admin ? 'Sim' : 'Não' }}</td>
                            <td class="p-3 text-right whitespace-nowrap">
                                @if($usuario->id !== auth()->id())
                                    <form action="{{ route('admin.usuarios.toggle', $usuario) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-blue-600">{{ $usuario->is_admin ? 'Remover admin' : 'Tornar admin' }}</button>
                                    </form>
                                    <form action="{{ route('admin.usuarios.destroy', $usuario) }}" method="POST" class="inline" onsubmit="return confirm('Remover este usuário?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 ml-2">Remover</button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-sm">(você)</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
