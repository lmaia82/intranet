<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Configurações</h2>
    </x-slot>
    <div class="py-6 max-w-2xl mx-auto sm:px-6 lg:px-8">
        @if(session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <div class="bg-white shadow rounded p-4">
            <div class="flex justify-between items-center gap-4">
                <div>
                    <p class="font-semibold">Prévia de funcionalidades na tela de login</p>
                    <p class="text-sm text-gray-500">
                        Mostra os blocos de Destaques, Aplicações, Mural de Avisos e demais
                        funcionalidades na página de login para visitantes não autenticados.
                        Reservado para um lançamento futuro — hoje, desativado, a tela de
                        login mostra só o fundo.
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.configuracoes.previa-login') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded text-white {{ $configuracao->previa_login_ativa ? 'bg-green-600' : 'bg-gray-400' }}">
                        {{ $configuracao->previa_login_ativa ? 'Ativada' : 'Desativada' }}
                    </button>
                </form>
            </div>
        </div>

        <div class="bg-white shadow rounded p-4 mt-4">
            <div class="flex justify-between items-center gap-4">
                <div>
                    <p class="font-semibold">Tempo de inatividade da sessão</p>
                    <p class="text-sm text-gray-500">
                        Depois de quantos minutos sem uso o usuário precisa fazer login novamente.
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.configuracoes.tempo-inatividade') }}" class="flex items-center gap-2">
                    @csrf
                    <input type="number" name="tempo_inatividade_minutos" min="1" max="43200"
                           value="{{ old('tempo_inatividade_minutos', $configuracao->tempo_inatividade_minutos) }}"
                           class="w-24 border-gray-300 rounded text-sm">
                    <span class="text-sm text-gray-500">minutos</span>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Salvar</button>
                </form>
            </div>
            @error('tempo_inatividade_minutos')
                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="bg-white shadow rounded p-4 mt-4">
            <div class="flex justify-between items-center gap-4">
                <div>
                    <p class="font-semibold">Aba Tutoriais</p>
                    <p class="text-sm text-gray-500">
                        Exibe a aba Tutoriais no menu e permite o acesso à página. Desative
                        para tirá-la do ar enquanto se avalia se ela vai continuar existindo.
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.configuracoes.tutoriais') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded text-white {{ $configuracao->tutoriais_ativo ? 'bg-green-600' : 'bg-gray-400' }}">
                        {{ $configuracao->tutoriais_ativo ? 'Ativada' : 'Desativada' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
