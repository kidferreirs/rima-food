<div class="w-64 min-h-screen bg-gray-900 text-white fixed">

    <div class="p-6 border-b border-gray-700">
        <h1 class="text-2xl font-bold">🍔 Rima Food</h1>
        <p class="text-sm text-gray-400">by Rimatech</p>

        @isset($restauranteAtual)
            <p class="text-xs text-green-400 mt-3">
                🟢 {{ $restauranteAtual->nome }}
            </p>
        @endisset
    </div>

    <nav class="p-4 space-y-2">

        @php
            $account = auth()->user()->account;
        @endphp

        @isset($restauranteAtual)

            <a href="{{ route('restaurante.dashboard', $restauranteAtual->slug) }}"
                class="block p-3 rounded hover:bg-gray-800">
                🏠 Dashboard
            </a>

            @if($account?->hasModule('cozinha'))
                <a href="{{ route('restaurante.cozinha.index', $restauranteAtual->slug) }}"
                    class="block p-3 rounded hover:bg-gray-800">
                    🍳 Cozinha
                </a>
            @endif

            @if($account?->hasModule('pedidos'))
                <a href="{{ route('restaurante.pedidos.index', $restauranteAtual->slug) }}"
                    class="block p-3 rounded hover:bg-gray-800">
                    🛒 Pedidos
                </a>
            @endif

            <a href="{{ route('restaurante.categorias.index', $restauranteAtual->slug) }}"
                class="block p-3 rounded hover:bg-gray-800">
                📂 Categorias
            </a>

            <a href="{{ route('restaurante.produtos.index', $restauranteAtual->slug) }}"
                class="block p-3 rounded hover:bg-gray-800">
                🍔 Produtos
            </a>

            <a href="{{ route('restaurante.cardapio', $restauranteAtual->slug) }}"
                class="block p-3 rounded hover:bg-gray-800">
                📱 Cardápio Digital
            </a>

            <a href="{{ route('restaurante.importacao.cardapio', $restauranteAtual->slug) }}"
                class="block p-3 rounded hover:bg-gray-800">
                📥 Importar Cardápio
            </a>

            <a href="{{ route('restaurante.clientes.index', $restauranteAtual->slug) }}"
                class="block p-3 rounded hover:bg-gray-800">
                👥 Clientes
            </a>

            @if($account?->hasModule('relatorios'))
                <a href="{{ route('restaurante.relatorios.index', $restauranteAtual->slug) }}"
                    class="block p-3 rounded hover:bg-gray-800">
                    📊 Relatórios
                </a>
            @endif

            <a href="{{ route('restaurantes.index') }}"
                class="block p-3 rounded hover:bg-gray-800 border-t border-gray-700 mt-4 pt-4">
                🏪 Trocar Restaurante
            </a>

        @else

            <a href="{{ route('dashboard') }}" class="block p-3 rounded hover:bg-gray-800">
                🏠 Dashboard
            </a>

            <a href="{{ route('restaurantes.index') }}" class="block p-3 rounded hover:bg-gray-800">
                🏪 Restaurantes
            </a>

        @endisset

    </nav>
</div>