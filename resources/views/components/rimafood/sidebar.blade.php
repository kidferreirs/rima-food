<aside
    x-cloak
    x-bind:class="menuAberto
        ? 'translate-x-0'
        : '-translate-x-full lg:translate-x-0'"
    class="
        fixed inset-y-0 left-0 z-50
        flex w-64 flex-col
        bg-gray-900 text-white
        shadow-2xl
        transition-transform duration-300 ease-in-out
        lg:z-30 lg:shadow-none
    "
>
    <div
        class="
            flex min-h-20 items-start justify-between
            border-b border-gray-700 p-5
        "
    >
        <div class="min-w-0">
            <h1 class="text-xl font-bold">
                Rima Food
            </h1>

            <p class="text-xs text-gray-400">
                by Rimatech
            </p>

            @isset($restauranteAtual)
                <p
                    class="
                        mt-3 truncate
                        text-xs font-medium text-green-400
                    "
                >
                    {{ $restauranteAtual->nome }}
                </p>
            @endisset
        </div>

        <button
            type="button"
            x-on:click="menuAberto = false"
            class="
                inline-flex h-9 w-9
                items-center justify-center
                rounded-lg text-gray-300
                transition hover:bg-gray-800 hover:text-white
                lg:hidden
            "
            aria-label="Fechar menu"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-6 w-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M6 18L18 6M6 6l12 12"
                />
            </svg>
        </button>
    </div>

    <nav
        class="
            flex-1 space-y-1
            overflow-y-auto
            p-4
        "
    >
        @php
            $account = auth()->user()?->account;
        @endphp

        @isset($restauranteAtual)

            <a
                href="{{ route(
                    'restaurante.dashboard',
                    $restauranteAtual->slug
                ) }}"
                x-on:click="menuAberto = false"
                class="
                    block rounded-xl px-4 py-3
                    text-sm font-medium
                    transition hover:bg-gray-800
                "
            >
                Dashboard
            </a>

            @if($account?->hasModule('cozinha'))
                <a
                    href="{{ route(
                        'restaurante.cozinha.index',
                        $restauranteAtual->slug
                    ) }}"
                    x-on:click="menuAberto = false"
                    class="
                        block rounded-xl px-4 py-3
                        text-sm font-medium
                        transition hover:bg-gray-800
                    "
                >
                    Cozinha
                </a>
            @endif

            @if($account?->hasModule('pedidos'))
                <a
                    href="{{ route(
                        'restaurante.pedidos.index',
                        $restauranteAtual->slug
                    ) }}"
                    x-on:click="menuAberto = false"
                    class="
                        block rounded-xl px-4 py-3
                        text-sm font-medium
                        transition hover:bg-gray-800
                    "
                >
                    Pedidos
                </a>
            @endif

            <a
                href="{{ route(
                    'restaurante.categorias.index',
                    $restauranteAtual->slug
                ) }}"
                x-on:click="menuAberto = false"
                class="
                    block rounded-xl px-4 py-3
                    text-sm font-medium
                    transition hover:bg-gray-800
                "
            >
                Categorias
            </a>

            <a
                href="{{ route(
                    'restaurante.produtos.index',
                    $restauranteAtual->slug
                ) }}"
                x-on:click="menuAberto = false"
                class="
                    block rounded-xl px-4 py-3
                    text-sm font-medium
                    transition hover:bg-gray-800
                "
            >
                Produtos
            </a>

            <a
                href="{{ route(
                    'restaurante.cardapio',
                    $restauranteAtual->slug
                ) }}"
                x-on:click="menuAberto = false"
                class="
                    block rounded-xl px-4 py-3
                    text-sm font-medium
                    transition hover:bg-gray-800
                "
            >
                Cardápio Digital
            </a>

            <a
                href="{{ route(
                    'restaurante.configuracoes.whatsapp.index',
                    $restauranteAtual->slug
                ) }}"
                x-on:click="menuAberto = false"
                class="
                    flex items-center justify-between
                    rounded-xl px-4 py-3
                    text-sm font-medium
                    transition hover:bg-gray-800
                "
            >
                <span>Conectar WhatsApp</span>

                @if($restauranteAtual->possuiWhatsappConectado())
                    <span
                        class="h-2.5 w-2.5 rounded-full bg-green-400"
                        title="WhatsApp conectado"
                    ></span>
                @endif
            </a>

            <a
                href="{{ route(
                    'restaurante.importacao.cardapio',
                    $restauranteAtual->slug
                ) }}"
                x-on:click="menuAberto = false"
                class="
                    block rounded-xl px-4 py-3
                    text-sm font-medium
                    transition hover:bg-gray-800
                "
            >
                Importar Cardápio
            </a>

            <a
                href="{{ route(
                    'restaurante.clientes.index',
                    $restauranteAtual->slug
                ) }}"
                x-on:click="menuAberto = false"
                class="
                    block rounded-xl px-4 py-3
                    text-sm font-medium
                    transition hover:bg-gray-800
                "
            >
                Clientes
            </a>

            @if($account?->hasModule('relatorios'))
                <a
                    href="{{ route(
                        'restaurante.relatorios.index',
                        $restauranteAtual->slug
                    ) }}"
                    x-on:click="menuAberto = false"
                    class="
                        block rounded-xl px-4 py-3
                        text-sm font-medium
                        transition hover:bg-gray-800
                    "
                >
                    Relatórios
                </a>
            @endif

        @else

            <a
                href="{{ route('dashboard') }}"
                x-on:click="menuAberto = false"
                class="
                    block rounded-xl px-4 py-3
                    text-sm font-medium
                    transition hover:bg-gray-800
                "
            >
                Dashboard
            </a>

        @endisset
    </nav>

    <div class="border-t border-gray-700 p-4">
        <form
            method="POST"
            action="{{ route('logout') }}"
        >
            @csrf

            <button
                type="submit"
                class="
                    w-full rounded-xl px-4 py-3
                    text-left text-sm font-medium
                    text-gray-300
                    transition hover:bg-gray-800
                    hover:text-white
                "
            >
                Sair
            </button>
        </form>
    </div>
</aside>