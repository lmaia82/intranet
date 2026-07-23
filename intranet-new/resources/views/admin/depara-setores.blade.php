<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">De/Para de Setores (AD)</h2>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if(session('status'))
            <div class="p-4 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
        @endif

        <p class="text-sm text-gray-500">
            Como ainda não é possível renomear os setores diretamente no Active Directory, use esta
            tela para ligar cada setor bruto trazido do AD ao setor já padronizado na intranet.
            Depois, use "Aplicar no cadastro de usuários" para preencher o setor dos usuários que
            ainda estão sem setor (intranet) definido — usuários que já têm um setor atribuído não
            são alterados.
        </p>

        @if($adSetores->isEmpty())
            <div class="bg-white shadow rounded p-6 text-sm text-gray-500">
                Nenhum setor do AD encontrado ainda entre os usuários importados.
            </div>
        @else
            <form method="POST" action="{{ route('admin.depara-setores.update') }}" class="bg-white shadow rounded p-6">
                @csrf
                @method('PUT')

                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="p-2">Setor no AD</th>
                            <th class="p-2">Setor na intranet</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($adSetores as $adSetor)
                            <tr class="border-b">
                                <td class="p-2 font-medium">{{ $adSetor }}</td>
                                <td class="p-2">
                                    <select name="mapeamentos[{{ $adSetor }}]" class="w-full border-gray-300 rounded">
                                        <option value="">(sem mapeamento)</option>
                                        @foreach($setores as $setor)
                                            <option value="{{ $setor->id }}" @selected(($mapeamentos[$adSetor] ?? null) == $setor->id)>
                                                {{ $setor->sigla }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <button type="submit" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded">Salvar mapeamento</button>
            </form>

            <form method="POST" action="{{ route('admin.depara-setores.aplicar') }}"
                    onsubmit="return confirm('Aplicar o mapeamento aos usuários que ainda estão sem setor definido na intranet?')">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">
                    Aplicar no cadastro de usuários
                </button>
            </form>
        @endif
    </div>
</x-app-layout>
