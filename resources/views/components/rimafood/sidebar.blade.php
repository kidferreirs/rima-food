<aside x-cloak x-bind:class="menuAberto
        ? 'translate-x-0'
        : '-translate-x-full lg:translate-x-0'" class="
        fixed inset-y-0 left-0 z-50
        flex w-72 flex-col
        bg-slate-950 text-white
        shadow-2xl
        transition-transform duration-300 ease-in-out
        lg:z-30 lg:shadow-none
    ">
    @php
        $account = auth()->user()?->account;

        $plano = $restauranteAtual->plano ?? 'MENU';

        $temPedidos = in_array(
            $plano,
            ['MENU_IA', 'FOOD'],
            true
        );

        $temWhatsapp = in_array(
            $plano,
            ['MENU_IA', 'FOOD'],
            true
        );

        $temCozinha = $plano === 'FOOD';
        $temRelatorios = $plano === 'FOOD';

        $linkClasses = function (bool $ativo): string {
            return $ativo
                ? 'bg-orange-500 text-white shadow-lg shadow-orange-950/20'
                : 'text-slate-300 hover:bg-slate-900 hover:text-white';
        };
    @endphp

    <div class="
            flex min-h-20 items-start justify-between
            border-b border-slate-800 p-5
        ">
        <div class="min-w-0">
            <div class="
                    flex items-center gap-1
                    font-black tracking-tight
                ">
                <span class="text-xl text-white">RIMA</span>

                <span class="ml-1 text-xl text-orange-500">
                    FOOD
                </span>
            </div>

            @isset($restauranteAtual)
                <p class="mt-3 text-xl font-bold text-orange-400 truncate">
                    {{ $restauranteAtual->nome }}
                </p>
            @endisset
        </div>

        <button type="button" x-on:click="menuAberto = false" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-400
                transition hover:bg-slate-900 hover:text-white lg:hidden" aria-label="Fechar menu">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav class="
            flex-1 overflow-y-auto
            px-3 py-4
            [scrollbar-width:none]
            [&::-webkit-scrollbar]:hidden
        ">
        @isset($restauranteAtual)

                <div class="space-y-1">
                    <a href="{{ route(
                'restaurante.dashboard',
                $restauranteAtual->slug
            ) }}" x-on:click="menuAberto = false" class="
                                        flex items-center gap-3
                                        rounded-xl py-1 px-2
                                        text-sm font-semibold
                                        transition
                                        {{ $linkClasses(
                request()->routeIs(
                    'restaurante.dashboard'
                )
            ) }}
                                    ">
                        <span class="text-base">🏠</span>
                        <span>Dashboard</span>
                    </a>
                </div>

                @if($temPedidos || $temCozinha)
                    <div class="my-4 border-t border-slate-800"></div>

                    <p class="px-4 pb-2 text-[10px] font-black uppercase tracking-[.18em] text-slate-600">
                        Operação
                    </p>

                    <div class="space-y-1">
                        @if($temPedidos && $account?->hasModule('pedidos'))
                            <a href="{{ route(
                                'restaurante.pedidos.index',
                                $restauranteAtual->slug
                            ) }}" x-on:click="menuAberto = false" class="
                                                            flex items-center gap-3
                                                            rounded-xl
                                                            py-1
                                                            px-2
                                                            text-sm font-semibold
                                                            transition
                                                            {{ $linkClasses(
                                request()->routeIs(
                                    'restaurante.pedidos.*'
                                )
                            ) }}
                                                        ">
                                <span class="text-base">🛒</span>
                                <span>Pedidos</span>
                            </a>
                        @endif

                        @if($temCozinha && $account?->hasModule('cozinha'))
                            <a href="{{ route(
                                'restaurante.cozinha.index',
                                $restauranteAtual->slug
                            ) }}" x-on:click="menuAberto = false" class="
                                                            flex items-center gap-3
                                                            rounded-xl
                                                            py-1
                                                            px-2
                                                            text-sm font-semibold
                                                            transition
                                                            {{ $linkClasses(
                                request()->routeIs(
                                    'restaurante.cozinha.*'
                                )
                            ) }}
                                                        ">
                                <span class="text-base">👨‍🍳</span>
                                <span>Cozinha</span>
                            </a>
                        @endif
                    </div>
                @endif

                <div class="my-4 border-t border-slate-800"></div>

                <p class="
                                    px-4 pb-2 text-[10px]
                                    font-black uppercase
                                    tracking-[.18em]
                                    text-slate-600
                                ">
                    Cardápio
                </p>

                <div class="space-y-1">
                    <a href="{{ route(
                'restaurante.produtos.index',
                $restauranteAtual->slug
            ) }}" x-on:click="menuAberto = false" class="
                                        flex items-center gap-3
                                        rounded-xl
                                        py-1
                                        px-2 
                                        text-sm font-semibold
                                        transition
                                        {{ $linkClasses(
                request()->routeIs(
                    'restaurante.produtos.*'
                )
            ) }}
                                    ">
                        <span class="text-base">🍔</span>
                        <span>Produtos</span>
                    </a>

                    <a href="{{ route(
                'restaurante.categorias.index',
                $restauranteAtual->slug
            ) }}" x-on:click="menuAberto = false" class="
                                        flex items-center gap-3
                                        rounded-xl
                                        py-1 px-2 
                                        text-sm font-semibold
                                        transition
                                        {{ $linkClasses(
                request()->routeIs(
                    'restaurante.categorias.*'
                )
            ) }}
                                    ">
                        <span class="text-base">📂</span>
                        <span>Categorias</span>
                    </a>

                    <a href="{{ route(
                'restaurante.cardapio',
                $restauranteAtual->slug
            ) }}" x-on:click="menuAberto = false" class="
                                        flex items-center gap-3
                                        rounded-xl
                                        py-1 px-2 
                                        text-sm font-semibold
                                        transition
                                        {{ $linkClasses(
                request()->routeIs(
                    'restaurante.cardapio'
                )
            ) }}
                                    ">
                        <span class="text-base">📱</span>
                        <span>Cardápio Digital</span>
                    </a>

                    <a href="{{ route(
                'restaurante.importacao.cardapio',
                $restauranteAtual->slug
            ) }}" x-on:click="menuAberto = false" class="
                                        flex items-center gap-3
                                        rounded-xl 
                                        py-1 px-2
                                        text-sm font-semibold
                                        transition
                                        {{ $linkClasses(
                request()->routeIs(
                    'restaurante.importacao.*'
                )
            ) }}
                                    ">
                        <span class="text-base">📥</span>
                        <span>Importar Cardápio</span>
                    </a>
                </div>

                <div class="my-4 border-t border-slate-800"></div>

                <p class="
                                    px-4 pb-2 text-[10px]
                                    font-black uppercase
                                    tracking-[.18em]
                                    text-slate-600
                                ">
                    Atendimento
                </p>

                <div class="space-y-1">
                    @if($temWhatsapp)
                            <a href="{{ route(
                            'restaurante.configuracoes.whatsapp.index',
                            $restauranteAtual->slug
                        ) }}" x-on:click="menuAberto = false" class="
                                                        flex items-center justify-between
                                                        rounded-xl
                                                        py-1 px-2 
                                                        text-sm font-semibold
                                                        transition
                                                        {{ $linkClasses(
                            request()->routeIs(
                                'restaurante.configuracoes.whatsapp.*'
                            )
                        ) }}
                                                    ">
                                <span class="flex items-center gap-3">
                                    <span class="text-base">💬</span>
                                    <span>WhatsApp com IA</span>
                                </span>

                                <span class="
                                                            h-2.5 w-2.5 rounded-full
                                                            {{
                            $restauranteAtual
                                ->possuiWhatsappConectado()
                            ? 'bg-green-400'
                            : 'bg-slate-600'
                                                            }}
                                                        " title="{{
                            $restauranteAtual
                                ->possuiWhatsappConectado()
                            ? 'WhatsApp conectado'
                            : 'WhatsApp desconectado'
                                                        }}"></span>
                            </a>
                    @endif

                    <a href="{{ route(
                'restaurante.clientes.index',
                $restauranteAtual->slug
            ) }}" x-on:click="menuAberto = false" class="
                                        flex items-center gap-3
                                        rounded-xl 
                                        py-1 px-2
                                        text-sm font-semibold
                                        transition
                                        {{ $linkClasses(
                request()->routeIs(
                    'restaurante.clientes.*'
                )
            ) }}
                                    ">
                        <span class="text-base">👥</span>
                        <span>Clientes</span>
                    </a>
                </div>

                <div class="my-4 border-t border-slate-800"></div>

                <p class="
                                    px-4 pb-2 text-[10px]
                                    font-black uppercase
                                    tracking-[.18em]
                                    text-slate-600
                                ">
                    Gestão
                </p>

                <div class="space-y-1">
                    <a href="{{ route(
                'restaurante.meu-restaurante.edit',
                $restauranteAtual->slug
            ) }}" x-on:click="menuAberto = false" class="
                                        flex items-center gap-3
                                        rounded-xl 
                                        py-1 px-2
                                        text-sm font-semibold
                                        transition
                                        {{ $linkClasses(
                request()->routeIs(
                    'restaurante.meu-restaurante.*'
                )
            ) }}
                                    ">
                        <span class="text-base">🏪</span>
                        <span>Meu Restaurante</span>
                    </a>

                    @if($temRelatorios && $account?->hasModule('relatorios'))
                        <a href="{{ route('restaurante.relatorios.index', $restauranteAtual->slug) }}" x-on:click="menuAberto = false" 
                           class=" flex items-center gap-3 rounded-xl py-1 px-2 text-sm font-semibold transition
                           {{ $linkClasses(
                                request()->routeIs('restaurante.relatorios.*')
                              )
                            }}
                        ">
                            <span class="text-base">📊</span>
                            <span>Relatórios</span>
                        </a>
                    @endif
                </div>

        @else

            <a href="{{ route('dashboard') }}" x-on:click="menuAberto = false" 
                class="flex items-center gap-3 rounded-xl text-sm font-semibold text-slate-300
                        transition hover:bg-slate-900 hover:text-white">
                <span>🏠</span>
                <span>Dashboard</span>
            </a>

        @endisset
    </nav>

    <div class="
            border-t border-slate-800
            p-4
        ">
        @isset($restauranteAtual)
            <div class="mb-3 rounded-xl bg-slate-900 border border-slate-800 p-3">
                <p class="text-[10px] font-black uppercase tracking-[.15em] text-slate-600">
                    Plano atual
                </p>

                <p class="mt-1 text-sm font-bold text-orange-300">
                    {{ match ($restauranteAtual->plano) {
                        'MENU' => 'Rima Menu',
                        'MENU_IA' => 'Rima Menu + IA',
                        'FOOD' => 'Rima Food',
                        default => 'Rima Food',
                        }
                    }}
                </p>
            </div>
        @endisset

        <div class="mt-3 rounded-xl bg-slate-900 border border-slate-800 overflow-hidden">

            <form method="POST" action="{{ route('logout') }}">

                @csrf

                <button type="submit" class="flex w-full px-4 py-3 items-center gap-3 text-left text-sm font-semibold
                        text-slate-300 transition hover:bg-slate-800 hover:text-white">
                    <span>↪</span>
                    <span>Sair</span>
                </button>

            </form>

        </div>

        <div class="mt-6 px-4">

            <div class="relative flex items-center justify-center">
                <div class="absolute left-0 w-[32%] border-t border-slate-800"></div>
                <a href="https://rimatech.cloud" target="_blank" rel="noopener noreferrer" 
                   class="px-3 bg-slate-950 text-[11px] text-slate-500 hover:text-orange-400 transition">
                    by Rimatech
                </a>
                <div class="absolute right-0 w-[32%] border-t border-slate-800"></div>
            </div>
        </div>
    </div>
</aside>