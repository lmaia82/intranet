<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Painel de Administração</h2>
    </x-slot>
    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8">
        @if(session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white shadow rounded p-4 text-center">
                <p class="text-3xl font-bold text-blue-600">{{ $stats['usuarios'] }}</p>
                <p class="text-sm text-gray-500">Usuários</p>
            </div>
            <div class="bg-white shadow rounded p-4 text-center">
                <p class="text-3xl font-bold text-blue-600">{{ $stats['setores'] }}</p>
                <p class="text-sm text-gray-500">Setores</p>
            </div>
            <div class="bg-white shadow rounded p-4 text-center">
                <p class="text-3xl font-bold text-blue-600">{{ $stats['grupos'] }}</p>
                <p class="text-sm text-gray-500">Grupos</p>
            </div>
            <div class="bg-white shadow rounded p-4 text-center">
                <p class="text-3xl font-bold text-blue-600">{{ $stats['ramais'] }}</p>
                <p class="text-sm text-gray-500">Ramais</p>
            </div>
            <div class="bg-white shadow rounded p-4 text-center">
                <p class="text-3xl font-bold text-blue-600">{{ $stats['informativos'] }}</p>
                <p class="text-sm text-gray-500">Informativos</p>
            </div>
            <div class="bg-white shadow rounded p-4 text-center">
                <p class="text-3xl font-bold text-blue-600">{{ $stats['eventos'] }}</p>
                <p class="text-sm text-gray-500">Eventos</p>
            </div>
            <div class="bg-white shadow rounded p-4 text-center">
                <p class="text-3xl font-bold text-blue-600">{{ $stats['destaques'] }}</p>
                <p class="text-sm text-gray-500">Destaques</p>
            </div>
            <div class="bg-white shadow rounded p-4 text-center">
                <p class="text-3xl font-bold text-blue-600">{{ $stats['arquivos'] }}</p>
                <p class="text-sm text-gray-500">Arquivos</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="{{ route('admin.setores') }}" class="bg-white shadow rounded p-6 hover:bg-gray-50">
                <p class="font-semibold text-lg">🏢 Gerenciar Setores</p>
                <p class="text-sm text-gray-500">Criar, editar e remover setores/departamentos</p>
            </a>
            <a href="{{ route('admin.usuarios') }}" class="bg-white shadow rounded p-6 hover:bg-gray-50">
                <p class="font-semibold text-lg">👤 Gerenciar Usuários</p>
                <p class="text-sm text-gray-500">Promover administradores, remover usuários, cadastro em lote</p>
            </a>
            <a href="{{ route('admin.grupos') }}" class="bg-white shadow rounded p-6 hover:bg-gray-50">
                <p class="font-semibold text-lg">🔐 Gerenciar Grupos</p>
                <p class="text-sm text-gray-500">Definir grupos com permissões de ver/criar por tela</p>
            </a>
            <a href="{{ route('admin.armazenamento') }}" class="bg-white shadow rounded p-6 hover:bg-gray-50">
                <p class="font-semibold text-lg">💾 Armazenamento por Setor</p>
                <p class="text-sm text-gray-500">Acompanhar consumo e cotas de espaço do Repositório</p>
            </a>
        </div>
    </div>
</x-app-layout>
