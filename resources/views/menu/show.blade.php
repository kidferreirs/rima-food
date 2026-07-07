<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rima Menu</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100">

    <main class="max-w-md mx-auto bg-white min-h-screen pb-10">

        <section class="relative">
            @if($restaurante->banner)

                <img src="{{ Storage::url($restaurante->banner) }}" class="w-full h-52 object-cover rounded-b-[2rem]">

            @else

                <img src="{{ asset('images/menu/cover1.png') }}" class="w-full h-52 object-cover rounded-b-[2rem]">

            @endif

        </section>

        <section class="px-5 -mt-10 relative z-10">
            <div class="w-20 h-20 bg-white rounded-3xl shadow-xl p-2 flex items-center justify-center">
                @if($restaurante->logo)
                    <img src="{{ Storage::url($restaurante->logo) }}" class="w-full h-full object-contain">
                @else
                    <img src="{{ asset('images/menu/logo.png') }}" class="w-120 h-full object-contain">
                @endif
            </div>

            <h1 class="text-3xl font-extrabold mt-4 text-slate-900">
                {{ $restaurante->nome }}
            </h1>

            <p class="text-slate-500 mt-1">
                Venda mais. Compartilhe em qualquer lugar.
            </p>

            <div class="flex flex-wrap gap-2 mt-4">
                <span class="bg-yellow-100 text-yellow-700 px-3 py-2 rounded-xl font-bold text-sm">
                    ⭐ 4.9
                </span>

                <span class="bg-slate-100 text-slate-600 px-3 py-2 rounded-xl font-bold text-sm">
                    📍 {{ $restaurante->cidade }}
                </span>

                <span class="bg-green-100 text-green-700 px-3 py-2 rounded-xl font-bold text-sm">
                    🟢 Aberto agora
                </span>
            </div>

            <div class="grid grid-cols-2 gap-3 mt-5">
                <a href="https://wa.me/55{{ preg_replace('/\D/', '', $restaurante->telefone) }}" target="_blank"
                    class="border border-slate-200 bg-white py-3 rounded-2xl text-center font-extrabold text-green-600 shadow-sm">
                    💬 WhatsApp
                </a>

                <button onclick="compartilharMenu()"
                    class="border border-slate-200 bg-white py-3 rounded-2xl text-center font-extrabold text-slate-800 shadow-sm">
                    📲 Compartilhar
                </button>
            </div>
        </section>

        <section class="px-5 mt-6">
            <div class="bg-slate-100 rounded-2xl px-4 py-3 flex items-center gap-3">
                <span class="text-xl">🔍</span>

                <input id="search" type="text" placeholder="O que você procura hoje?"
                    class="bg-transparent border-none outline-none focus:ring-0 w-full text-slate-700 placeholder:text-slate-400">
            </div>
        </section>

        <section class="px-5 mt-6">
            <div class="flex gap-3 overflow-x-auto pb-2">

                @foreach($restaurante->categorias as $categoria)

                    <button
                        onclick="document.getElementById('categoria-{{ $categoria->id }}').scrollIntoView({behavior:'smooth'})"
                        class="bg-slate-100 text-slate-700 px-5 py-3 rounded-2xl font-bold whitespace-nowrap">

                        {{ $categoria->nome }}

                    </button>

                @endforeach

            </div>
        </section>

        @foreach($restaurante->categorias as $categoria)

            <section id="categoria-{{ $categoria->id }}" class="px-5 mt-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-extrabold text-slate-900">
                        {{ $categoria->nome }}
                    </h2>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    @foreach($categoria->produtos as $produto)

                        <div class="produto-card bg-white border rounded-3xl overflow-hidden shadow-sm"
                            data-nome="{{ strtolower($produto->nome) }}" data-descricao="{{ strtolower($produto->descricao) }}">

                            <div class="h-36 bg-orange-100 flex items-center justify-center text-5xl relative overflow-hidden">

                                @if($produto->imagem)
                                    <img src="{{ Storage::url($produto->imagem) }}" class="w-full h-full object-cover"
                                        alt="{{ $produto->nome }}">
                                @else
                                    🍔
                                @endif

                            </div>

                            <div class="p-4">
                                <h3 class="font-extrabold text-slate-900">
                                    {{ $produto->nome }}
                                </h3>

                                <p class="text-xs text-slate-500 mt-1">
                                    {{ $produto->descricao }}
                                </p>

                                <div class="flex justify-between items-center mt-3">
                                    <span class="font-extrabold text-green-600">
                                        R$ {{ number_format($produto->preco, 2, ',', '.') }}
                                    </span>

                                    <button type="button"
                                        class="add-cart w-9 h-9 bg-green-500 text-white rounded-full font-bold text-xl"
                                        data-id="{{ $produto->id }}" data-nome="{{ $produto->nome }}"
                                        data-preco="{{ $produto->preco }}+id="search">
                                        +
                                    </button>

                                </div>
                            </div>
                        </div>

                    @endforeach
                </div>
            </section>

        @endforeach

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
        let cart = JSON.parse(localStorage.getItem('rima-cart-{{ $restaurante->slug }}')) || [];

        const buttons = document.querySelectorAll('.add-cart');
        const cartBar = document.getElementById('cart-bar');
        const cartItems = document.getElementById('cart-items');
        const cartTotal = document.getElementById('cart-total');

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

            const totalItens = cart.reduce((soma, item) => soma + item.quantidade, 0);
            const totalValor = cart.reduce((soma, item) => soma + (item.preco * item.quantidade), 0);

            cartItems.innerText = totalItens;
            cartTotal.innerText = totalValor.toFixed(2).replace('.', ',');

            if (totalItens > 0) {
                cartBar.classList.remove('hidden');
            } else {
                cartBar.classList.add('hidden');
            }

            // Salva o carrinho atualizado
            localStorage.setItem('rima-cart-{{ $restaurante->slug }}', JSON.stringify(cart));
        }
        atualizarCarrinho();

        cartBar.addEventListener('click', () => {
            window.location.href = "{{ route('menu.checkout', $restaurante->slug) }}";
        });

        function compartilharMenu() {

            if (navigator.share) {

                navigator.share({
                    title: document.title,
                    text: 'Confira nosso cardápio!',
                    url: window.location.href
                });

            } else {

                navigator.clipboard.writeText(window.location.href);

                alert('✅ Link copiado!');

            }

        }

        document
            .getElementById('search')
            .addEventListener('input', function () {

                const texto = this.value.toLowerCase();

                document
                    .querySelectorAll('.produto-card')
                    .forEach(card => {

                        const nome = card.dataset.nome;

                        const descricao = card.dataset.descricao;

                        if (
                            nome.includes(texto)
                            ||
                            descricao.includes(texto)
                        ) {

                            card.style.display = '';

                        } else {

                            card.style.display = 'none';

                        }

                    });

            });
    </script>
</body>

</html>