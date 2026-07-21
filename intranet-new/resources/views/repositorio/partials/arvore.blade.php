<ul class="{{ $nivel > 0 ? 'ml-3 pl-2 border-l border-gray-200' : '' }}">
    @foreach($pastas as $pasta)
        <li>
            <div
                x-data="{ aberto: {{ in_array($pasta['id'], $pastasAbertas) ? 'true' : 'false' }} }"
                class="flex items-center gap-1 rounded {{ $pastaAtualId == $pasta['id'] ? 'bg-blue-100' : 'hover:bg-gray-100' }}"
            >
                @if(count($pasta['filhas']))
                    <button type="button" @click="aberto = !aberto" class="w-4 shrink-0 text-gray-400 text-xs leading-none">
                        <span x-text="aberto ? '▾' : '▸'"></span>
                    </button>
                @else
                    <span class="w-4 shrink-0"></span>
                @endif
                <a
                    href="{{ route('repositorio.index', ['pasta' => $pasta['id']]) }}"
                    class="flex-1 flex items-center gap-1 py-1 pr-2 text-sm truncate {{ $pastaAtualId == $pasta['id'] ? 'font-semibold text-blue-800' : 'text-gray-700' }}"
                    title="{{ $pasta['nome'] }}"
                >
                    <span>📁</span> {{ $pasta['nome'] }}
                </a>
            </div>
            @if(count($pasta['filhas']))
                <div x-show="aberto" x-cloak>
                    @include('repositorio.partials.arvore', ['pastas' => $pasta['filhas'], 'pastaAtualId' => $pastaAtualId, 'pastasAbertas' => $pastasAbertas, 'nivel' => $nivel + 1])
                </div>
            @endif
        </li>
    @endforeach
</ul>
