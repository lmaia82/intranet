@php
    $tutoriaisAtivo = \App\Models\Configuracao::atual()->tutoriais_ativo;
    $usuario = auth()->user();

    // Ordem de prioridade: quando não couber tudo numa linha só, os
    // últimos da lista são os primeiros a ir pro dropdown "Mais" — ver
    // Alpine.data('priorityNav', ...) em resources/js/app.js.
    $navItems = collect([
        ['label' => __('Início'), 'href' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
        $usuario->hasPermission('informativos.ver') ? ['label' => __('Informativos'), 'href' => route('informativos.index'), 'active' => request()->routeIs('informativos.*')] : null,
        $usuario->hasPermission('eventos.ver') ? ['label' => __('Agenda'), 'href' => route('eventos.index'), 'active' => request()->routeIs('eventos.*')] : null,
        $usuario->hasPermission('salas.ver') ? ['label' => __('Reserva de Sala'), 'href' => route('reservas-sala.index'), 'active' => request()->routeIs('reservas-sala.*')] : null,
        $usuario->hasPermission('repositorio.ver') ? ['label' => __('Repositório'), 'href' => route('repositorio.index'), 'active' => request()->routeIs('repositorio.*')] : null,
        $usuario->hasPermission('organograma.ver') ? ['label' => __('Organograma'), 'href' => route('organograma.index'), 'active' => request()->routeIs('organograma.*')] : null,
        $usuario->hasPermission('ramais.ver') ? ['label' => __('Ramais'), 'href' => route('telefones.index'), 'active' => request()->routeIs('telefones.*')] : null,
        $usuario->hasPermission('destaques.ver') ? ['label' => __('Destaques'), 'href' => route('destaques.index'), 'active' => request()->routeIs('destaques.*')] : null,
        ($tutoriaisAtivo && $usuario->hasPermission('tutoriais.ver')) ? ['label' => __('Tutoriais'), 'href' => route('tutoriais.index'), 'active' => request()->routeIs('tutoriais.*')] : null,
        ['label' => __('Publicações'), 'href' => route('artigos.index'), 'active' => request()->routeIs('artigos.*')],
        ['label' => __('Aplicações'), 'href' => route('onlyoffice.aplicacoes'), 'active' => request()->routeIs('onlyoffice.aplicacoes')],
        $usuario->is_admin ? ['label' => __('Administração'), 'href' => route('admin.index'), 'active' => request()->routeIs('admin.*')] : null,
    ])->filter()->values();
@endphp
<nav x-data="{ open: false }" class="bg-gradient-to-b from-[#B9DBF7] to-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex flex-1 min-w-0">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <img src="{{ asset('images/logo-cetem.png') }}" alt="CETEM - Centro de Tecnologia Mineral" class="h-10 w-auto">
                    </a>
                </div>

                <!-- Navigation Links: preenche a barra com quantos itens couberem, o resto vai pro "Mais" -->
                <div class="hidden xl:flex xl:items-center xl:ms-8 flex-1 min-w-0" x-data="priorityNav(@js($navItems))">
                    <!-- Clone de medição: fora da tela, mede a largura real de cada item em tamanho normal -->
                    <div class="fixed flex items-center gap-4 invisible pointer-events-none" style="top: -9999px; left: -9999px;" aria-hidden="true" x-ref="medidor">
                        <template x-for="item in items" :key="'medidor-' + item.href">
                            <span class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-bold leading-5 whitespace-nowrap" x-text="item.label" data-nav-item></span>
                        </template>
                    </div>

                    <div class="flex flex-1 items-center gap-4 overflow-hidden min-w-0" x-ref="itemsBox">
                        <template x-for="item in itensVisiveis" :key="item.href">
                            <a :href="item.href" x-text="item.label"
                                class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-bold leading-5 whitespace-nowrap transition duration-150 ease-in-out focus:outline-none"
                                :class="item.active ? 'border-[#166F9E] text-[#166F9E]' : 'border-transparent text-[#166F9E] hover:opacity-75 hover:border-gray-300'">
                            </a>
                        </template>
                    </div>

                    <!-- Mais: sempre reserva o espaço no layout (só fica "invisible" quando não há overflow), pra medição não entrar em vaivém -->
                    <div class="relative flex items-center shrink-0 ms-4" :class="hasOverflow ? '' : 'invisible pointer-events-none'" x-data="{ maisAberto: false }" @click.outside="maisAberto = false">
                        <button @click="maisAberto = ! maisAberto" type="button" :tabindex="hasOverflow ? 0 : -1"
                            class="inline-flex items-center gap-1 px-1 pt-1 border-b-2 border-transparent text-sm font-bold leading-5 text-[#166F9E] whitespace-nowrap hover:opacity-75 hover:border-gray-300 transition duration-150 ease-in-out focus:outline-none">
                            {{ __('Mais') }}
                            <svg class="h-4 w-4 shrink-0 transition-transform" :class="{ 'rotate-180': maisAberto }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div x-show="maisAberto" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute z-50 top-full mt-2 start-0 w-48 rounded-md shadow-lg" style="display: none;" @click="maisAberto = false">
                            <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white">
                                <template x-for="item in itensOverflow" :key="item.href">
                                    <a :href="item.href" x-text="item.label" class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"></a>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden xl:flex xl:items-center xl:ms-6 shrink-0">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150 whitespace-nowrap">
                            <div>{{ explode(' ', Auth::user()->name)[0] }}</div>

                            <div class="ms-1 shrink-0">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Perfil') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Sair') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center xl:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden xl:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Início') }}
            </x-responsive-nav-link>
            @if(auth()->user()->hasPermission('organograma.ver'))
                <x-responsive-nav-link :href="route('organograma.index')" :active="request()->routeIs('organograma.*')">
                    {{ __('Organograma') }}
                </x-responsive-nav-link>
            @endif
            @if(auth()->user()->hasPermission('ramais.ver'))
                <x-responsive-nav-link :href="route('telefones.index')" :active="request()->routeIs('telefones.*')">
                    {{ __('Ramais') }}
                </x-responsive-nav-link>
            @endif
            @if(auth()->user()->hasPermission('destaques.ver'))
                <x-responsive-nav-link :href="route('destaques.index')" :active="request()->routeIs('destaques.*')">
                    {{ __('Destaques') }}
                </x-responsive-nav-link>
            @endif
            @if(auth()->user()->hasPermission('informativos.ver'))
                <x-responsive-nav-link :href="route('informativos.index')" :active="request()->routeIs('informativos.*')">
                    {{ __('Informativos') }}
                </x-responsive-nav-link>
            @endif
            @if(auth()->user()->hasPermission('eventos.ver'))
                <x-responsive-nav-link :href="route('eventos.index')" :active="request()->routeIs('eventos.*')">
                    {{ __('Agenda') }}
                </x-responsive-nav-link>
            @endif
            @if(auth()->user()->hasPermission('salas.ver'))
                <x-responsive-nav-link :href="route('reservas-sala.index')" :active="request()->routeIs('reservas-sala.*')">
                    {{ __('Reserva de Sala') }}
                </x-responsive-nav-link>
            @endif
            @if($tutoriaisAtivo && auth()->user()->hasPermission('tutoriais.ver'))
                <x-responsive-nav-link :href="route('tutoriais.index')" :active="request()->routeIs('tutoriais.*')">
                    {{ __('Tutoriais') }}
                </x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('artigos.index')" :active="request()->routeIs('artigos.*')">
                {{ __('Publicações') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('onlyoffice.aplicacoes')" :active="request()->routeIs('onlyoffice.aplicacoes')">
                {{ __('Aplicações') }}
            </x-responsive-nav-link>
            @if(auth()->user()->hasPermission('repositorio.ver'))
                <x-responsive-nav-link :href="route('repositorio.index')" :active="request()->routeIs('repositorio.*')">
                    {{ __('Repositório') }}
                </x-responsive-nav-link>
            @endif
            @if(auth()->user()->is_admin)
                <x-responsive-nav-link :href="route('admin.index')" :active="request()->routeIs('admin.*')">
                    {{ __('Administração') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Perfil') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Sair') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
