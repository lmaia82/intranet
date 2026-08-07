<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nova Reserva de Sala</h2>
    </x-slot>
    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8">
        @php $action = route('reservas-sala.store'); $method = null; @endphp
        @include('reservas-sala._form')
    </div>
</x-app-layout>
