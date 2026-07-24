@php
    $chefia = $usuarios->filter(fn ($u) => !is_null($u->prioridadeChefia()))->sortBy(fn ($u) => $u->prioridadeChefia())->values();
    $demais = $usuarios->filter(fn ($u) => is_null($u->prioridadeChefia()))->sortBy('name')->values();
@endphp

@if($usuarios->isEmpty())
    <p class="text-gray-400">Nenhum usuário vinculado a este setor.</p>
@else
    @if($chefia->isNotEmpty())
        <p class="font-semibold text-[10px] uppercase text-gray-500">Chefia</p>
        @foreach($chefia as $usuario)
            <p class="truncate">
                <span class="font-medium">{{ $usuario->name }}</span>
                @if($usuario->cargo)
                    <span class="text-gray-500">({{ $usuario->cargo }})</span>
                @endif
                —
                <a href="mailto:{{ \Illuminate\Support\Str::lower($usuario->email) }}" class="text-blue-700 underline">{{ \Illuminate\Support\Str::lower($usuario->email) }}</a>
            </p>
        @endforeach
    @endif

    @if($demais->isNotEmpty())
        <p class="font-semibold text-[10px] uppercase text-gray-500 mt-2">Demais colaboradores</p>
        @foreach($demais as $usuario)
            <p class="truncate">
                <span class="font-medium">{{ $usuario->name }}</span>
                @if($usuario->cargo)
                    <span class="text-gray-500">({{ $usuario->cargo }})</span>
                @endif
                —
                <a href="mailto:{{ \Illuminate\Support\Str::lower($usuario->email) }}" class="text-blue-700 underline">{{ \Illuminate\Support\Str::lower($usuario->email) }}</a>
            </p>
        @endforeach
    @endif
@endif
