<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Reserva de Sala</h2>
    </x-slot>
    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8">
        @php $action = route('reservas-sala.update', $reserva); $method = 'PUT'; @endphp
        @include('reservas-sala._form')

        <form action="{{ route('reservas-sala.destroy', $reserva) }}" method="POST" onsubmit="return confirm('Excluir esta reserva definitivamente?')" class="mt-4">
            @csrf
            @method('DELETE')
            <button type="submit" class="w-full py-2 px-4 border border-red-300 text-red-700 rounded font-semibold hover:bg-red-50">Excluir Reserva</button>
        </form>
    </div>
</x-app-layout>
