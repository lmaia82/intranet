@php
    $tutoriaisAtivo = \App\Models\Configuracao::atual()->tutoriais_ativo;
    $maisAtivo = request()->routeIs(['organograma.*', 'telefones.*', 'destaques.*', 'tutoriais.*', 'artigos.*', 'onlyoffice.aplicacoes']);
@endphp
<nav x-data="{ open: false }" class="bg-gradient-to-b from-[#B9DBF7] to-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <img src="{{ asset('images/logo-cetem.png') }}" alt="CETEM - Centro de Tecnologia Mineral" class="h-10 w-auto">
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-4 xl:-my-px xl:ms-8 xl:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="whitespace-nowrap">
                        {{ __('Início') }}
                    </x-nav-link>
                    @if(auth()->user()->hasPermission('informativos.ver'))
                        <x-nav-link :href="route('informativos.index')" :active="request()->routeIs('informativos.*')" class="whitespace-nowrap">
                            {{ __('Informativos') }}
                        </x-nav-link>
                    @endif
                    @if(auth()->user()->hasPermission('eventos.ver'))
                        <x-nav-link :href="route('eventos.index')" :active="request()->routeIs('eventos.*')" class="whitespace-nowrap">
                            {{ __('Agenda') }}
                        </x-nav-link>
                    @endif
                    @if(auth()->user()->hasPermission('salas.ver'))
                        <x-nav-link :href="route('reservas-sala.index')" :active="request()->routeIs('reservas-sala.*')" class="whitespace-nowrap">
                            {{ __('Reserva de Sala') }}
                        </x-nav-link>
                    @endif
                    @if(auth()->user()->hasPermission('repositorio.ver'))
                        <x-nav-link :href="route('repositorio.index')" :active="request()->routeIs('repositorio.*')" class="whitespace-nowrap">
                            {{ __('Repositório') }}
                        </x-nav-link>
                    @endif

                    <!-- Mais: itens usados com menos frequência, agrupados para o menu sempre caber numa linha só -->
                    <div class="relative flex items-center" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = ! open" type="button"
                            class="inline-flex items-center gap-1 px-1 pt-1 border-b-2 text-sm font-bold leading-5 whitespace-nowrap transition duration-150 ease-in-out focus:outline-none
                                {{ $maisAtivo ? 'border-[#166F9E] text-[#166F9E]' : 'border-transparent text-[#166F9E] hover:opacity-75 hover:border-gray-300' }}">
                            {{ __('Mais') }}
                            <svg class="h-4 w-4 shrink-0 transition-transform" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute z-50 top-full mt-2 start-0 w-48 rounded-md shadow-lg" style="display: none;" @click="open = false">
                            <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white">
                                @if(auth()->user()->hasPermission('organograma.ver'))
                                    <x-dropdown-link :href="route('organograma.index')">{{ __('Organograma') }}</x-dropdown-link>
                                @endif
                                @if(auth()->user()->hasPermission('ramais.ver'))
                                    <x-dropdown-link :href="route('telefones.index')">{{ __('Ramais') }}</x-dropdown-link>
                                @endif
                                @if(auth()->user()->hasPermission('destaques.ver'))
                                    <x-dropdown-link :href="route('destaques.index')">{{ __('Destaques') }}</x-dropdown-link>
                                @endif
                                @if($tutoriaisAtivo && auth()->user()->hasPermission('tutoriais.ver'))
                                    <x-dropdown-link :href="route('tutoriais.index')">{{ __('Tutoriais') }}</x-dropdown-link>
                                @endif
                                <x-dropdown-link :href="route('artigos.index')">{{ __('Publicações') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('onlyoffice.aplicacoes')">{{ __('Aplicações') }}</x-dropdown-link>
                            </div>
                        </div>
                    </div>

                    @if(auth()->user()->is_admin)
                        <x-nav-link :href="route('admin.index')" :active="request()->routeIs('admin.*')" class="whitespace-nowrap">
                            {{ __('Administração') }}
                        </x-nav-link>
                    @endif
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
