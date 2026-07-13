<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Novo informativo</h2>
    </x-slot>
    <div class="py-6 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('informativos.store') }}" enctype="multipart/form-data" class="bg-white shadow rounded p-6 space-y-4">
            @csrf
            @include('informativos._form')
            <p class="text-sm text-gray-500">
                Ao publicar, um e-mail será enviado automaticamente para todos os usuários
                ou, se um setor for selecionado acima, apenas para os usuários daquele setor.
            </p>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Publicar</button>
        </form>
    </div>
</x-app-layout>
