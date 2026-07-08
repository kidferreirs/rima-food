<x-rimafood.layout>

    <div class="p-8 max-w-4xl">

        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold">✏️ Editar Pedido #{{ $pedido->numero_pedido ?? $pedido->id  }}</h1>

            <a href="{{ route('restaurante.dashboard', $restaurante->slug) }}" class="text-gray-600 hover:underline">
                🏠 Voltar ao Dashboard
            </a>
        </div>

        <div class="bg-white rounded-lg p-4 border mb-6">
            🏪 Restaurante: <strong>{{ $restaurante->nome }}</strong>
            <br>
            📦 Status atual: <strong>{{ ucfirst(str_replace('_', ' ', $pedido->status)) }}</strong>
        </div>

        <form action="{{ route('restaurante.pedidos.update', [$restaurante->slug, $pedido]) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <select name="cliente_id" class="w-full border rounded-lg p-3" required>
                @foreach($clientes as $cliente)
                    <option value="{{ $cliente->id }}" @selected($pedido->cliente_id === $cliente->id)>
                        {{ $cliente->nome }} - {{ $cliente->telefone }}
                    </option>
                @endforeach
            </select>

            <div id="itens" class="space-y-3">

                @foreach($pedido->itens as $item)
                    <div class="item grid grid-cols-1 md:grid-cols-3 gap-3">

                        <select name="produto_id[]" class="produto w-full border rounded-lg p-3" required>
                            @foreach($produtos as $produto)
                                <option value="{{ $produto->id }}" data-preco="{{ $produto->preco }}"
                                    @selected($item->produto_id === $produto->id)>
                                    {{ $produto->nome }} - R$ {{ number_format($produto->preco, 2, ',', '.') }}
                                </option>
                            @endforeach
                        </select>

                        <input type="number" name="quantidade[]" class="quantidade w-full border rounded-lg p-3" min="1"
                            value="{{ $item->quantidade }}" required>

                        <button type="button" class="remover bg-red-500 text-white rounded-lg px-4 py-3">
                            Remover
                        </button>

                    </div>
                @endforeach

            </div>

            <button type="button" id="adicionar" class="bg-blue-500 text-white px-5 py-3 rounded-lg">
                + Adicionar produto
            </button>

            <div>
                <label class="block font-semibold mb-2">

                    💰 Forma de pagamento

                </label>

                <select name="forma_pagamento" class="w-full border rounded-lg p-3" required>

                    <option value="dinheiro" @selected($pedido->forma_pagamento === 'dinheiro')>
                        💵 Dinheiro
                    </option>

                    <option value="cartao" @selected($pedido->forma_pagamento === 'cartao')>
                        💳 Cartão
                    </option>

                    <option value="pix" @selected($pedido->forma_pagamento === 'pix')>
                        🏦 Pix
                    </option>

                </select>
            </div>

            <div class="bg-white border rounded-xl p-4 space-y-4">

                <h3 class="font-bold text-lg">
                    🚚 Entrega
                </h3>

                <div>
                    <label class="block font-semibold mb-2">
                        Tipo de entrega
                    </label>

                    <select name="tipo_entrega" id="tipo_entrega" class="w-full border rounded-lg p-3" required>
                        <option value="balcao">🏪 Balcão</option>
                        <option value="retirada">🛍️ Retirada</option>
                        <option value="entrega">🚚 Entrega</option>
                    </select>
                </div>

                <div id="campos_entrega" class="hidden space-y-4">

                    <input type="number" step="0.01" min="0" name="taxa_entrega" id="taxa_entrega"
                        placeholder="Taxa de entrega" class="w-full border rounded-lg p-3">

                    <textarea name="endereco_entrega" id="endereco_entrega" placeholder="Endereço de entrega"
                        class="w-full border rounded-lg p-3" rows="3"></textarea>

                </div>

            </div>

            <label class="flex items-center gap-3 bg-yellow-50 border border-yellow-200 rounded-xl p-4 cursor-pointer">
                <input type="checkbox" name="prioritario" value="1" class="w-5 h-5" @checked($pedido->prioritario)>

                <span class="font-semibold text-yellow-800">
                    ⭐ Pedido prioritário
                </span>
            </label>

            <textarea name="observacao" placeholder="Observação do pedido..." class="w-full border rounded-lg p-3"
                rows="3">{{ $pedido->observacao }}</textarea>

            <div class="bg-white rounded-lg p-4 border text-xl font-bold">
                💰 Total do pedido:
                <span id="total">R$ 0,00</span>
            </div>

            <button class="bg-green-500 text-white px-6 py-3 rounded-lg">
                Salvar Alterações
            </button>
        </form>

    </div>

    <script>
        const itens = document.getElementById('itens');
        const adicionar = document.getElementById('adicionar');
        const total = document.getElementById('total');

        function calcularTotal() {
            let valorTotal = 0;

            document.querySelectorAll('.item').forEach(function (item) {
                const produto = item.querySelector('.produto');
                const quantidade = item.querySelector('.quantidade');

                const preco = parseFloat(produto.options[produto.selectedIndex].dataset.preco || 0);
                const qtd = parseInt(quantidade.value || 1);

                valorTotal += preco * qtd;
            });

            total.textContent = valorTotal.toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            });
        }

        function ativarEventos() {
            document.querySelectorAll('.produto, .quantidade').forEach(function (campo) {
                campo.removeEventListener('change', calcularTotal);
                campo.removeEventListener('input', calcularTotal);

                campo.addEventListener('change', calcularTotal);
                campo.addEventListener('input', calcularTotal);
            });

            document.querySelectorAll('.remover').forEach(function (botao) {
                botao.onclick = function () {
                    if (document.querySelectorAll('.item').length > 1) {
                        botao.closest('.item').remove();
                        calcularTotal();
                    }
                }
            });
        }

        adicionar.addEventListener('click', function () {
            const primeiroItem = document.querySelector('.item');
            const novoItem = primeiroItem.cloneNode(true);

            novoItem.querySelector('.produto').selectedIndex = 0;
            novoItem.querySelector('.quantidade').value = 1;

            itens.appendChild(novoItem);

            ativarEventos();
            calcularTotal();
        });

        ativarEventos();
        calcularTotal();
    </script>

</x-rimafood.layout>