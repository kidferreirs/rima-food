<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rima Menu</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-slate-100">
    @php
        $abertoAgora = false;
        $statusFuncionamento = '🔴 Fechado agora';

        if ($restaurante->abre_as && $restaurante->fecha_as) {

            $agora = now()->format('H:i');
            $abre = \Carbon\Carbon::parse($restaurante->abre_as)->format('H:i');
            $fecha = \Carbon\Carbon::parse($restaurante->fecha_as)->format('H:i');

            if ($abre <= $fecha) {
                $abertoAgora = $agora >= $abre && $agora <= $fecha;
            } else {
                $abertoAgora = $agora >= $abre || $agora <= $fecha;
            }

            if ($abertoAgora) {
                $statusFuncionamento = '🟢 Aberto até ' . $fecha;
            } else {
                $statusFuncionamento = '🔴 Abre às ' . $abre;
            }
        }

        $enderecoMaps = trim(
            ($restaurante->endereco ?? '') . ' ' .
            ($restaurante->numero ?? '') . ' ' .
            ($restaurante->cidade ?? '') . ' ' .
            ($restaurante->estado ?? '')
        );

        $linkMaps = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($enderecoMaps);
    @endphp

    <header id="header-compacto" class="fixed top-0 left-1/2 -translate-x-1/2 z-[70] w-full max-w-md bg-white border-b border-slate-200
           shadow-md opacity-0 -translate-y-5 pointer-events-none transition-all duration-300">

        <div class="flex items-center gap-3 px-4 py-3">
            <div class="w-11 h-11 shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                @if($restaurante->logo)
                    <img src="{{ Storage::url($restaurante->logo) }}" alt="{{ $restaurante->nome }}"
                        class="w-full h-full object-contain">
                @else
                    <img src="{{ asset('images/menu/logo.png') }}" alt="{{ $restaurante->nome }}"
                        class="w-full h-full object-contain">
                @endif
            </div>

            <div class="min-w-0 flex-1">
                <p class="font-extrabold text-slate-900 truncate">{{ $restaurante->nome }}</p>
                <p class="text-xs text-slate-500">{{ $statusFuncionamento }}</p>
            </div>

            <button id="header-compacto-carrinho" type="button" class="relative w-11 h-11 shrink-0 rounded-xl bg-slate-900 text-white shadow-md
                    flex items-center justify-center" aria-label="Abrir carrinho">
                <span class="text-lg">🛒</span>

                <span id="header-compacto-contador" class="hidden absolute -top-2 -right-2 min-w-6 h-6 px-1 rounded-full border-2 border-white bg-green-500
                      text-xs font-extrabold text-white items-center justify-center">
                    0
                </span>
            </button>
        </div>
    </header>

    <main class="max-w-md mx-auto bg-white min-h-screen pb-10" data-restaurante-slug="{{ $restaurante->slug }}"
        data-garcom-url="{{ route('menu.garcom.conversar', $restaurante->slug) }}"
        data-garcom-liberado="{{ $restaurante->temIA() ? '1' : '0' }}">

        @if($restaurante->banner)
            <img src="{{ Storage::url($restaurante->banner) }}" class="w-full h-52 object-cover rounded-b-[2rem]">
        @else
            <img src="{{ asset('images/menu/cover1.png') }}" class="w-full h-52 object-cover rounded-b-[2rem]">
        @endif

        <section class="px-5 -mt-10 relative z-10">

            <div class="w-24 h-24 bg-white rounded-3xl shadow-xl flex items-center justify-center overflow-hidden">
                @if($restaurante->logo)
                    <img src="{{ Storage::url($restaurante->logo) }}" class="w-24 h-24 object-contain scale-120">
                @else
                    <img src="{{ asset('images/menu/logo.png') }}" class="w-24 h-24 object-contain scale-120">
                @endif
            </div>

            <h1 class="text-3xl font-extrabold mt-4 text-slate-900"> {{ $restaurante->nome }} </h1>

            <p class="text-slate-500 mt-1"> Venda mais. Compartilhe em qualquer lugar.</p>

            <div class="flex flex-wrap gap-4 mt-4">
                @if($restaurante->google_rating)
                    <a href="{{ $restaurante->google_maps_url ?: $linkMaps }}" target="_blank"
                        class="bg-yellow-100 text-yellow-700 px-3 py-2 rounded-xl font-bold text-sm">
                        ⭐ {{ number_format($restaurante->google_rating, 1) }}
                        @if($restaurante->google_reviews_total)
                            ({{ $restaurante->google_reviews_total }})
                        @endif
                    </a>
                @endif

                <a href="{{ $linkMaps }}" target="_blank"
                    class="bg-slate-100 text-slate-600 px-3 py-2 rounded-xl font-bold text-sm">
                    📍{{ $restaurante->cidade }}
                </a>
                <span
                    class="{{ $abertoAgora ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} px-3 py-2 rounded-xl font-bold text-sm">
                    {{ $statusFuncionamento }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-3 mt-5">
                @php
                    $mensagem = urlencode(
                        "Olá! Vi o cardápio da {$restaurante->nome} pelo Rima Menu e gostaria de fazer um pedido."
                    );
                @endphp

                <a href="https://wa.me/55{{ preg_replace('/\D/', '', $restaurante->telefone) }}?text={{ $mensagem }}"
                    class="border border-slate-200 bg-white py-3 rounded-2xl text-center font-extrabold text-green-600 shadow-sm"
                    target="_blank"> 💬 WhatsApp</a>

                <button onclick="compartilharMenu()"
                    class="border border-slate-200 bg-white py-3 rounded-2xl text-center font-extrabold text-slate-800 shadow-sm">
                    📲 Compartilhar
                </button>
            </div>
        </section>

        <section class="px-5 mt-6">
            <div class="bg-slate-100 rounded-2xl px-4 py-4 flex items-center gap-3">
                <span class="text-xl">🔍</span>
                <input id="search" type="text" placeholder="Pesquise produtos, ingredientes ou categorias..."
                    autocomplete="off"
                    class="bg-transparent border-none outline-none focus:ring-0 w-full text-slate-700 placeholder:text-slate-400">
            </div>

            <div id="garcom-container" class="hidden opacity-0 translate-y-2 transition-all duration-300 mt-4">
                <div class="flex gap-2 overflow-x-auto pb-3">
                    <button class="garcom-pergunta bg-white border rounded-full px-4 py-2 whitespace-nowrap"
                        data-pergunta="Quais pizzas vocês têm?">
                        🍕 Pizzas
                    </button>

                    <button class="garcom-pergunta bg-white border rounded-full px-4 py-2 whitespace-nowrap"
                        data-pergunta="Qual é o produto mais barato?">
                        💰 Mais barato
                    </button>

                    <button class="garcom-pergunta bg-white border rounded-full px-4 py-2 whitespace-nowrap"
                        data-pergunta="Tem algo sem lactose?">
                        🥛 Sem lactose
                    </button>
                </div>

                <div id="chat-garcom">

                    <div class="bg-green-600 text-white px-4 py-3 flex items-start justify-between gap-3">

                        <div>
                            <h3 class="font-extrabold"> 🤖 Garçom Inteligente </h3>
                        </div>

                        <button id="fechar-chat-garcom" type="button"
                            class="w-9 h-9 shrink-0 rounded-full bg-white/15 hover:bg-white/25"
                            aria-label="Recolher Garçom Inteligente">
                            ✕
                        </button>
                    </div>

                    <div id="chat-mensagens" class="max-h-72 overflow-y-auto p-4 space-y-3 bg-slate-50 rounded-b-2xl">
                        <div class="flex">
                            <div class="bg-white rounded-2xl px-4 py-3 shadow-sm max-w-[90%]">
                                <p class="font-bold text-green-700"> Garçom </p>
                                <p class="text-sm text-slate-700 mt-1">
                                    Olá! 👋
                                    Posso ajudar você a escolher.
                                    Pergunte, por exemplo:
                                    • Quais pizzas vocês têm?
                                    • Qual é o produto mais barato?
                                    • Tem algo sem lactose?
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="navegacao-categorias" class="px-5 mt-7">

            <button id="abrir-menu-categorias" type="button" class="w-full flex items-center justify-between
                    bg-slate-900 text-white rounded-2xl px-5 py-4 shadow-sm font-extrabold">
                <span class="flex items-center gap-3">
                    <span class="text-xl">☰</span>
                    Categorias
                </span>

                <span class="text-slate-300">{{ $restaurante->categorias->count() }}</span>
            </button>
        </section>

        @if($destaques->isNotEmpty())
            <section id="destaques-section" class="px-5 mt-8">

                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-2xl font-extrabold text-slate-900">⭐ Destaques</h2>
                        <p class="text-sm text-slate-500 mt-1"> Os favoritos da casa para você.</p>
                    </div>
                </div>

                <div class="flex gap-4 overflow-x-auto pb-3">
                    @foreach($destaques as $produto)
                        <article class="min-w-[190px] max-w-[190px] bg-white border rounded-3xl overflow-hidden shadow-sm">
                            <div class="h-32 bg-orange-100 flex items-center justify-center text-5xl overflow-hidden">
                                @if($produto->imagem)
                                    <img src="{{ Storage::url($produto->imagem) }}" class="w-full h-full object-cover"
                                        alt="{{ $produto->nome }}">

                                @else
                                    🍔
                                @endif
                            </div>

                            <div class="p-4">
                                <span
                                    class="inline-block bg-orange-50 text-orange-700 rounded-full px-2 py-1 text-xs font-bold mb-2">
                                    ⭐ Destaque
                                </span>

                                <h3 class="font-extrabold text-slate-900">{{ $produto->nome }}</h3>
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $produto->descricao }}</p>
                                <div class="flex justify-between items-center mt-4">
                                    <span class="font-extrabold text-green-600">R$
                                        {{ number_format($produto->preco, 2, ',', '.') }}</span>
                                    <button type="button"
                                        class="add-cart w-9 h-9 bg-green-500 text-white rounded-full font-bold text-xl"
                                        data-id="{{ $produto->id }}" data-nome="{{ $produto->nome }}"
                                        data-preco="{{ $produto->preco }}">
                                        +
                                    </button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <section id="favoritos-section" class="hidden px-5 mt-8">
            <h2 class="text-2xl font-extrabold text-slate-900"> ❤️ Seus favoritos
                <span id="favoritos-contador" class="text-base text-slate-400"> (0) </span>
            </h2>

            <p class="text-sm text-slate-500 mt-1 mb-4">Produtos que você salvou neste dispositivo.</p>
            <div id="favoritos-lista" class="space-y-3"></div>
        </section>

        <div id="nenhum-produto-encontrado" class="hidden mx-5 mt-8 bg-slate-50 rounded-2xl p-8 text-center">
            <div class="text-4xl mb-3"> 😕 </div>
            <p class="font-bold text-slate-800"> Nenhum produto encontrado </p>
            <p class="text-sm text-slate-500 mt-2"> Tente outro produto, ingrediente ou categoria. </p>
        </div>

        @foreach($restaurante->categorias as $categoria)
            <section id="categoria-{{ $categoria->id }}" class="categoria-section px-5 mt-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-extrabold text-slate-900">{{ $categoria->nome }}</h2>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    @foreach($categoria->produtos as $produto)
                        <div class="produto-card relative bg-white border rounded-3xl overflow-hidden shadow-sm"
                            data-produto-id="{{ $produto->id }}" data-produto-preco="{{ $produto->preco }}"
                            data-nome="{{ strtolower($produto->nome ?? '') }}"
                            data-descricao="{{ strtolower($produto->descricao ?? '') }}"
                            data-palavras="{{ strtolower($produto->palavras_chave ?? '') }}"
                            data-sinonimos="{{ strtolower($produto->sinonimos ?? '') }}"
                            data-ingredientes="{{ strtolower($produto->ingredientes ?? '') }}"
                            data-tags="{{ strtolower($produto->tags ?? '') }}"
                            data-categoria="{{ strtolower($categoria->nome ?? '') }}">

                            <button type="button" class="toggle-favorito absolute top-3 right-3 z-10 w-9 h-9 bg-white/90 rounded-full shadow
                                            flex items-center justify-center text-xl" data-produto-id="{{ $produto->id }}"
                                aria-label="Favoritar {{ $produto->nome }}">
                                🤍
                            </button>

                            <div class="h-36 bg-orange-100 flex items-center justify-center text-5xl relative overflow-hidden">
                                @if($produto->imagem)
                                    <img src="{{ Storage::url($produto->imagem) }}" class="w-full h-full object-cover"
                                        alt="{{ $produto->nome }}">
                                @else
                                    🍔
                                @endif
                            </div>

                            <div class="p-4">
                                <h3 class="font-extrabold text-slate-900">{{ $produto->nome }}</h3>
                                <p class="text-xs text-slate-500 mt-1">{{ $produto->descricao }}</p>

                                <div class="flex justify-between items-center mt-3">
                                    <span class="font-extrabold text-green-600">R$
                                        {{ number_format($produto->preco, 2, ',', '.') }}</span>
                                    <button type="button"
                                        class="add-cart w-9 h-9 bg-green-500 text-white rounded-full font-bold text-xl"
                                        data-id="{{ $produto->id }}" data-nome="{{ $produto->nome }}"
                                        data-preco="{{ $produto->preco }}">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>    
                    @endforeach
                </div>
            </section>
        @endforeach

        <div id="menu-categorias-overlay" class="hidden fixed inset-0 z-[80]" aria-hidden="true">
            <button id="fundo-menu-categorias" type="button" class="absolute inset-0 bg-slate-950/50"
                aria-label="Fechar categorias"></button>

            <aside id="menu-categorias-painel" class="absolute left-0 top-0 h-full w-[85%] max-w-sm bg-white shadow-2xl p-5 -translate-x-full 
                transition-transform duration-300">
                <div class="flex items-center justify-between pb-5 border-b">
                    <div>
                        <h2 class="text-2xl font-extrabold text-slate-900"> ☰ Categorias </h2>
                        <p class="text-sm text-slate-500 mt-1"> Escolha uma seção do cardápio. </p>
                    </div>

                    <button id="fechar-menu-categorias" type="button"
                        class="w-10 h-10 rounded-full bg-slate-100 text-slate-700 font-bold"
                        aria-label="Fechar categorias">
                        ✕
                    </button>
                </div>

                <nav class="space-y-2 mt-5">
                    @foreach($restaurante->categorias as $categoria)
                        <button type="button" class="categoria-menu-link w-full flex items-center justify-between bg-slate-50 hover:bg-green-50
                                    rounded-2xl px-4 py-4 text-left transition"
                            data-categoria-alvo="categoria-{{ $categoria->id }}">
                            <span class="font-bold text-slate-800">
                                {{ $categoria->nome }}
                            </span>
                            <span class="text-slate-400">
                                →
                            </span>
                        </button>
                    @endforeach
                </nav>
            </aside>
        </div>

        @if($maisVendidos->isNotEmpty())
            <section id="mais-vendidos-section" class="px-5 mt-8">
                <h2 class="text-2xl font-extrabold text-slate-900"> 🔥 Mais vendidos </h2>
                <p class="text-sm text-slate-500 mt-1 mb-4"> Os produtos que os clientes mais pedem. </p>

                <div class="flex gap-4 overflow-x-auto pb-3">
                    @foreach($maisVendidos as $produto)
                        <article class="min-w-[190px] max-w-[190px] bg-white border rounded-3xl overflow-hidden shadow-sm">
                            <div class="h-32 bg-orange-100 flex items-center justify-center overflow-hidden">
                                @if($produto->imagem)
                                    <img src="{{ Storage::url($produto->imagem) }}" class="w-full h-full object-cover"
                                        alt="{{ $produto->nome }}">
                                @else
                                    <span class="text-5xl">🍔</span>
                                @endif
                            </div>

                            <div class="p-4">
                                <span class="inline-block bg-red-50 text-red-700 rounded-full px-2 py-1 text-xs font-bold mb-2">
                                    🔥 {{ $produto->total_vendido }} vendidos
                                </span>
                                <h3 class="font-extrabold text-slate-900">{{ $produto->nome }}</h3>
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $produto->descricao }}</p>

                                <div class="flex justify-between items-center mt-4">
                                    <span class="font-extrabold text-green-600">
                                        R$ {{ number_format($produto->preco, 2, ',', '.') }}
                                    </span>

                                    <button type="button"
                                        class="add-cart w-9 h-9 bg-green-500 text-white rounded-full font-bold text-xl"
                                        data-id="{{ $produto->id }}" data-nome="{{ $produto->nome }}"
                                        data-preco="{{ $produto->preco }}">
                                        +
                                    </button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <div id="toast"
            class="hidden fixed bottom-24 left-1/2 -translate-x-1/2 bg-slate-900 text-white px-5 py-3 rounded-2xl shadow-xl z-50">
        </div>

        <div id="cart-bar"
            class="hidden fixed bottom-4 left-1/2 -translate-x-1/2 w-[90%] max-w-sm bg-slate-900 text-white rounded-2xl shadow-2xl p-4 z-50">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-bold">🛒 Ver pedido</p>
                    <p class="text-sm text-slate-300">
                        <span id="cart-items">0</span> itens
                    </p>
                </div>
                <div class="font-extrabold text-green-400">
                    R$ <span id="cart-total">0,00</span>
                </div>
            </div>
        </div>

        <footer class="text-center text-xs text-slate-400 mt-8">
            Powered by <strong>Rimatech</strong>
        </footer>
    </main>

    <script>
        let cart = JSON.parse(
            localStorage.getItem('rima-cart-{{ $restaurante->slug }}')
        ) || [];

        const buttons = document.querySelectorAll('.add-cart');

        const cartBar = document.getElementById('cart-bar');
        const cartItems = document.getElementById('cart-items');
        const cartTotal = document.getElementById('cart-total');

        const headerCompacto = document.getElementById('header-compacto');

        const headerCompactoCarrinho = document.getElementById(
            'header-compacto-carrinho'
        );

        const headerCompactoContador = document.getElementById(
            'header-compacto-contador'
        );


        buttons.forEach(button => {
            button.addEventListener('click', () => {
                const id = button.dataset.id;
                const nome = button.dataset.nome;
                const preco = parseFloat(button.dataset.preco);

                const item = cart.find(produto => produto.id === id);

                if (item) {
                    item.quantidade++;
                } else {
                    cart.push({
                        id,
                        nome,
                        preco,
                        quantidade: 1
                    });
                }

                atualizarCarrinho();
            });
        });

        function atualizarCarrinho() {
            const totalItens = cart.reduce(
                (soma, item) => soma + item.quantidade,
                0
            );

            const totalValor = cart.reduce(
                (soma, item) => soma + (item.preco * item.quantidade),
                0
            );

            cartItems.innerText = totalItens;
            headerCompactoContador.innerText = totalItens;

            if (totalItens > 0) {
                headerCompactoContador.classList.remove('hidden');
                headerCompactoContador.classList.add('flex');
            } else {
                headerCompactoContador.classList.add('hidden');
                headerCompactoContador.classList.remove('flex');
            }

            cartTotal.innerText = totalValor
                .toFixed(2)
                .replace('.', ',');

            if (totalItens > 0) {
                cartBar.classList.remove('hidden');
            } else {
                cartBar.classList.add('hidden');
            }

            localStorage.setItem(
                'rima-cart-{{ $restaurante->slug }}',
                JSON.stringify(cart)
            );
        }

        atualizarCarrinho();

        cartBar.addEventListener('click', () => {
            window.location.href =
                "{{ route('menu.checkout', $restaurante->slug) }}";
        });

        headerCompactoCarrinho.addEventListener('click', () => {
            window.location.href = "{{ route('menu.checkout', $restaurante->slug) }}";
        });

        function compartilharMenu() {
            if (navigator.share) {
                navigator.share({
                    title: document.title,
                    text: 'Confira nosso cardápio!',
                    url: window.location.href
                }).catch(() => {
                    // Usuário pode cancelar o compartilhamento.
                });

                return;
            }

            navigator.clipboard
                .writeText(window.location.href)
                .then(() => {
                    alert('✅ Link copiado!');
                });
        }

        function atualizarHeaderCompacto() {
            const mostrar = window.scrollY > 220;

            headerCompacto.classList.toggle('opacity-0', !mostrar);
            headerCompacto.classList.toggle('-translate-y-5', !mostrar);
            headerCompacto.classList.toggle('pointer-events-none', !mostrar);

            headerCompacto.classList.toggle('opacity-100', mostrar);
            headerCompacto.classList.toggle('translate-y-0', mostrar);
            headerCompacto.classList.toggle('pointer-events-auto', mostrar);
        }
        window.addEventListener(
            'scroll',
            atualizarHeaderCompacto,
            { passive: true }
        );

        atualizarHeaderCompacto();
    </script>
</body>

</html>