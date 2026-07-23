<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Perfil') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg space-y-6">
                <div>
                    <p class="text-sm font-medium text-gray-500">Nome</p>
                    <p class="mt-1 text-gray-900">{{ $user->name }}</p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">E-mail</p>
                    <p class="mt-1 text-gray-900">{{ $user->email }}</p>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-500">Lotação</p>
                    <p class="mt-1 text-gray-900">
                        @if($user->sector)
                            {{ $user->sector->nome ?: $user->sector->sigla }} ({{ $user->sector->caminhoHierarquico() }})
                        @else
                            Sem lotação definida
                        @endif
                    </p>
                </div>

                <p class="text-sm text-gray-500">
                    Para alterar essas informações, entre em contato com um administrador da intranet.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
