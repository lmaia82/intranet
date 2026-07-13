<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Novo grupo</h2>
    </x-slot>
    <div class="py-6 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('admin.grupos.store') }}" class="bg-white shadow rounded p-6 space-y-4">
            @csrf
            @include('admin._grupo-form')
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Criar grupo</button>
        </form>
    </div>
</x-app-layout>
