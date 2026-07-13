<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seu Pedido</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100">

    <div class="max-w-md mx-auto bg-white min-h-screen p-5 pb-10">

        <div class="relative -mx-5 -mt-5 mb-6">
            <div class="h-40 overflow-hidden">
                @if($restaurante->banner)
                    <img src="{{ asset('storage/' . $restaurante->banner) }}" class="w-full h-full object-cover">
                @else
                    <img src="{{ asset('images/menu/cover1.png') }}" class="w-full h-full object-cover">
                @endif

            </div>

            <div
                class="absolute -bottom-6 left-5 w-20 h-20 bg-white rounded-3xl shadow-lg flex items-center justify-center overflow-hidden">

                @if($restaurante->logo)
                    <img src="{{ asset('storage/' . $restaurante->logo) }}" class="w-full h-full object-cover">
                @else
                    🍔
                @endif
            </div>
        </div>

        <div class="mt-10">

            <h1 class="text-3xl font-extrabold">{{ $restaurante->nome }}</h1>
            <p class="text-slate-500">Revise seu pedido antes de finalizar.</p>
        </div>

        @if(session('error'))
            <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-5">
                {{ session('error') }}
            </div>
        @endif

        <div id="lista-produtos" class="space-y-4"></div>

        <div class="border-t pt-5 mt-6 mb-6">
            <div class="flex justify-between text-xl font-bold">
                <span>Subtotal</span>
                <span id="total">R$ 0,00</span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 mb-6">
            <a href="{{ route('menu.show', $restaurante->slug) }}"
                class="text-center border rounded-2xl py-3 font-bold">
                🍔 Continuar Comprando
            </a>

            <button type="button" onclick="limparPedido()" class="border rounded-2xl py-3 font-bold text-red-500">
                🧹 Limpar Carrinho
            </button>
        </div>

        <form action="{{ route('menu.pedido.store', $restaurante->slug) }}" method="POST" class="space-y-4">
            @csrf

            <input type="hidden" name="carrinho" id="carrinho">

            <div>
                <label class="font-semibold text-sm">Nome *</label>
                <input name="nome" required class="w-full border rounded-xl p-3 mt-1" placeholder="Seu nome">
            </div>

            <div>
                <label class="font-semibold text-sm">Telefone *</label>
                <input name="telefone" required class="w-full border rounded-xl p-3 mt-1" placeholder="Seu WhatsApp">
            </div>

            <div>
                <label class="font-semibold text-sm">🚚 Tipo de entrega *</label>
                <select name="tipo_entrega" required class="w-full border rounded-xl p-3 mt-1">
                    <option value="retirada">Retirada no balcão</option>
                    <option value="balcao">Consumo no local / Mesa</option>
                    <option value="entrega">Delivery</option>
                </select>
            </div>

            <div>
                <label class="font-semibold text-sm">💰 Forma de pagamento *</label>
                <select name="forma_pagamento" required class="w-full border rounded-xl p-3 mt-1">
                    <option value="pix">Pix</option>
                    <option value="dinheiro">Dinheiro</option>
                    <option value="credito">Cartão de Crédito</option>
                    <option value="debito">Cartão de Débito</option>
                </select>
            </div>

            <div>
                <label class="font-semibold text-sm">Observações</label>
                <textarea name="observacao" class="w-full border rounded-xl p-3 mt-1" rows="3"
                    placeholder="Ex: sem cebola, ponto da carne, mesa 4..."></textarea>
            </div>

            <button class="w-full bg-green-500 hover:bg-green-600 text-white font-bold rounded-2xl py-4">
                🚀 Enviar Pedido
            </button>
        </form>

    </div>

    <script>
        const cartKey = 'rima-cart-{{ $restaurante->slug }}';

        let cart = JSON.parse(localStorage.getItem(cartKey)) || [];

        const lista = document.getElementById('lista-produtos');
        const total = document.getElementById('total');
        const form = document.querySelector('form');
        const inputCarrinho = document.getElementById('carrinho');

        function salvarCarrinho() {
            localStorage.setItem(cartKey, JSON.stringify(cart));
        }

        function formatarMoeda(valor) {
            return 'R$ ' + valor.toFixed(2).replace('.', ',');
        }

        function renderCarrinho() {
            lista.innerHTML = '';

            if (cart.length === 0) {
                lista.innerHTML = `
                <div class="text-center text-slate-500 bg-slate-50 rounded-2xl p-6">
                    Seu carrinho está vazio.
                </div>
            `;

                total.innerText = 'R$ 0,00';
                return;
            }

            let valorTotal = 0;

            cart.forEach((item, index) => {
                valorTotal += item.preco * item.quantidade;

                lista.innerHTML += `
                <div class="border rounded-2xl p-4">
                    <div class="font-bold text-slate-900">
                        ${item.nome}
                    </div>

                    <div class="text-sm text-slate-500 mt-1">
                        ${formatarMoeda(item.preco)}
                    </div>

                    <div class="flex items-center justify-between mt-4">
                        <div class="flex items-center gap-3">
                            <button type="button"
                                onclick="diminuirItem(${index})"
                                class="w-9 h-9 rounded-full bg-slate-100 font-bold">
                                -
                            </button>

                            <span class="font-bold">
                                ${item.quantidade}
                            </span>

                            <button type="button"
                                onclick="aumentarItem(${index})"
                                class="w-9 h-9 rounded-full bg-green-500 text-white font-bold">
                                +
                            </button>
                        </div>

                        <div class="font-extrabold text-green-600">
                            ${formatarMoeda(item.preco * item.quantidade)}
                        </div>
                    </div>

                    <button type="button"
                        onclick="removerItem(${index})"
                        class="text-red-500 text-sm font-bold mt-4">
                        🗑 Remover item
                    </button>
                </div>
            `;
            });

            total.innerText = formatarMoeda(valorTotal);
        }

        function aumentarItem(index) {
            cart[index].quantidade++;
            salvarCarrinho();
            renderCarrinho();
        }

        function diminuirItem(index) {
            cart[index].quantidade--;

            if (cart[index].quantidade <= 0) {
                cart.splice(index, 1);
            }

            salvarCarrinho();
            renderCarrinho();
        }

        function removerItem(index) {
            cart.splice(index, 1);
            salvarCarrinho();
            renderCarrinho();
        }

        function limparPedido() {
            if (!confirm('Deseja limpar o pedido?')) {
                return;
            }

            cart = [];
            salvarCarrinho();
            renderCarrinho();
        }

        form.addEventListener('submit', function (event) {
            if (cart.length === 0) {
                event.preventDefault();
                alert('Seu carrinho está vazio.');
                return;
            }

            inputCarrinho.value = JSON.stringify(cart);
            localStorage.removeItem(cartKey);
        });

        renderCarrinho();
    </script>

</body>

</html>