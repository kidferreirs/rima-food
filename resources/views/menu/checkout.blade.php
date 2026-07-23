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

        <div class="border-t pt-5 mt-6 mb-6 space-y-3">
            <div class="flex justify-between font-semibold">
                <span>Subtotal</span>
                <span id="subtotal">R$ 0,00</span>
            </div>

            <div id="linha-entrega" class="hidden flex justify-between font-semibold">
                <span>Entrega</span>
                <span id="taxa-entrega">R$ 0,00</span>
            </div>

            <div id="linha-distancia" class="hidden flex justify-between text-sm text-slate-500">
                <span>Distância</span>
                <span id="distancia-entrega">0 km</span>
            </div>

            <div id="linha-tempo" class="hidden flex justify-between text-sm text-slate-500">
                <span>Tempo estimado</span>
                <span id="tempo-entrega">0 min</span>
            </div>

            <div class="flex justify-between text-xl font-bold border-t pt-3">
                <span>Total</span>
                <span id="total">R$ 0,00</span>
            </div>

            <div id="mensagem-entrega" class="hidden text-sm rounded-xl p-3"></div>
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
                <select id="tipo_entrega" name="tipo_entrega" required class="w-full border rounded-xl p-3 mt-1">
                    <option value="retirada">Retirada no balcão</option>
                    <option value="balcao">Consumo no local / Mesa</option>
                    <option value="entrega">Delivery</option>
                </select>
            </div>

            <div id="bloco-endereco" class="hidden space-y-3">
                <div>
                    <label>CEP</label>
                    <input id="cep" name="cep" class="w-full border rounded-xl p-3" placeholder="99999-999">
                </div>

                <div>
                    <label>Endereço</label>
                    <input id="logradouro" name="logradouro" readonly class="w-full border rounded-xl p-3 bg-gray-100">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label>Número</label>
                        <input id="numero" name="numero" class="w-full border rounded-xl p-3">
                    </div>

                    <div>
                        <label>Complemento</label>
                        <input id="complemento" name="complemento" class="w-full border rounded-xl p-3">
                    </div>
                </div>

                <div>
                    <label>Bairro</label>
                    <input id="bairro" name="bairro" readonly class="w-full border rounded-xl p-3 bg-gray-100">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label>Cidade</label>
                        <input id="cidade" name="cidade" readonly class="w-full border rounded-xl p-3 bg-gray-100">
                    </div>

                    <div>
                        <label>Estado</label>
                        <input id="estado" name="estado" readonly class="w-full border rounded-xl p-3 bg-gray-100">
                    </div>
                </div>
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
        let subtotalCarrinho = 0;
        let taxaEntregaAtual = 0;
        let entregaCalculada = false;

        const lista = document.getElementById('lista-produtos');
        const subtotalEl = document.getElementById('subtotal');
        const totalEl = document.getElementById('total');
        const taxaEntregaEl = document.getElementById('taxa-entrega');
        const distanciaEntregaEl = document.getElementById('distancia-entrega');
        const tempoEntregaEl = document.getElementById('tempo-entrega');
        const linhaTempo = document.getElementById('linha-tempo');
        const linhaEntrega = document.getElementById('linha-entrega');
        const linhaDistancia = document.getElementById('linha-distancia');
        const mensagemEntrega = document.getElementById('mensagem-entrega');

        const form = document.querySelector('form');
        const inputCarrinho = document.getElementById('carrinho');

        const tipoEntrega = document.getElementById('tipo_entrega');
        const blocoEndereco = document.getElementById('bloco-endereco');

        const cep = document.getElementById('cep');
        const logradouro = document.getElementById('logradouro');
        const numero = document.getElementById('numero');
        const complemento = document.getElementById('complemento');
        const bairro = document.getElementById('bairro');
        const cidade = document.getElementById('cidade');
        const estado = document.getElementById('estado');

        function salvarCarrinho() {
            localStorage.setItem(cartKey, JSON.stringify(cart));
        }

        function formatarMoeda(valor) {
            return 'R$ ' + Number(valor).toFixed(2).replace('.', ',');
        }

        function atualizarResumo() {
            subtotalEl.innerText = formatarMoeda(subtotalCarrinho);

            const isEntrega = tipoEntrega.value === 'entrega';
            const totalFinal = subtotalCarrinho + (isEntrega ? taxaEntregaAtual : 0);

            totalEl.innerText = formatarMoeda(totalFinal);
            linhaTempo.classList.toggle('hidden', !isEntrega || !entregaCalculada);
            linhaEntrega.classList.toggle('hidden', !isEntrega || !entregaCalculada);
            linhaDistancia.classList.toggle('hidden', !isEntrega || !entregaCalculada);
        }

        function renderCarrinho() {
            lista.innerHTML = '';
            subtotalCarrinho = 0;

            if (cart.length === 0) {
                lista.innerHTML = `
                <div class="text-center text-slate-500 bg-slate-50 rounded-2xl p-6">
                    Seu carrinho está vazio.
                </div>
            `;

                subtotalEl.innerText = 'R$ 0,00';
                totalEl.innerText = 'R$ 0,00';
                return;
            }

            cart.forEach((item, index) => {
                subtotalCarrinho += item.preco * item.quantidade;

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

            atualizarResumo();
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

        function atualizarBlocoEndereco() {
            const isEntrega = tipoEntrega.value === 'entrega';

            blocoEndereco.classList.toggle('hidden', !isEntrega);

            blocoEndereco.querySelectorAll('input').forEach(input => {
                input.required = isEntrega && input.name !== 'complemento';
            });

            if (!isEntrega) {
                taxaEntregaAtual = 0;
                entregaCalculada = false;
                mensagemEntrega.classList.add('hidden');
            }

            atualizarResumo();
        }

        function limparEndereco() {
            logradouro.value = '';
            bairro.value = '';
            cidade.value = '';
            estado.value = '';
            taxaEntregaAtual = 0;
            entregaCalculada = false;
            mensagemEntrega.classList.add('hidden');
            atualizarResumo();
        }

        async function calcularEntrega() {
            if (
                tipoEntrega.value !== 'entrega' ||
                !cep.value ||
                !logradouro.value ||
                !numero.value ||
                !bairro.value ||
                !cidade.value ||
                !estado.value
            ) {
                return;
            }

            mensagemEntrega.className =
                'text-sm rounded-xl p-3 bg-blue-50 text-blue-700';

            mensagemEntrega.innerText = 'Calculando entrega...';
            mensagemEntrega.classList.remove('hidden');

            entregaCalculada = false;
            atualizarResumo();

            try {
                const response = await fetch(
                    `{{ route('menu.delivery.quote', $restaurante->slug) }}`,
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            cep: cep.value,
                            logradouro: logradouro.value,
                            numero: numero.value,
                            complemento: complemento.value,
                            bairro: bairro.value,
                            cidade: cidade.value,
                            estado: estado.value
                        })
                    }
                );
                const json = await response.json();

                if (!response.ok || !json.success) {
                    throw new Error(
                        json.message || 'Não foi possível calcular a entrega.'
                    );
                }
                taxaEntregaAtual = Number(json.data.taxa);
                entregaCalculada = true;
                taxaEntregaEl.innerText = formatarMoeda(taxaEntregaAtual);
                distanciaEntregaEl.innerText = Number(json.data.distancia_km).toFixed(2).replace('.', ',') + ' km';
                // tempoEntregaEl.innerText = json.data.tempo_minutos + ' min';
                mensagemEntrega.className = 'text-sm rounded-xl p-3 bg-green-50 text-green-700';
                mensagemEntrega.innerText = `Entrega calculada: ${json.data.faixa}.`;
                atualizarResumo();
            } catch (error) {
                taxaEntregaAtual = 0;
                entregaCalculada = false;
                mensagemEntrega.className = 'text-sm rounded-xl p-3 bg-red-50 text-red-700';
                mensagemEntrega.innerText = error.message || 'Não foi possível calcular a entrega.';
                atualizarResumo();
            }
        }

        tipoEntrega.addEventListener('change', atualizarBlocoEndereco);

        cep.addEventListener('input', function () {
            let valor = cep.value.replace(/\D/g, '').slice(0, 8);

            if (valor.length > 5) {
                valor = valor.replace(/^(\d{5})(\d+)/, '$1-$2');
            }

            cep.value = valor;
        });

        cep.addEventListener('blur', async () => {
            const valor = cep.value.replace(/\D/g, '');

            limparEndereco();

            if (valor.length !== 8) {
                if (valor.length > 0) {
                    alert('Digite um CEP válido com 8 números.');
                }

                return;
            }

            cep.disabled = true;
            cep.classList.add('bg-slate-100');
            cep.value = 'Buscando...';

            try {
                const response = await fetch(
                    `{{ route('viacep.buscar', ['cep' => '__CEP__']) }}`
                        .replace('__CEP__', valor),
                    {
                        headers: {
                            Accept: 'application/json'
                        }
                    }
                );

                if (!response.ok) {
                    throw new Error('CEP não encontrado.');
                }

                const json = await response.json();

                if (!json.success || !json.data) {
                    throw new Error('CEP não encontrado.');
                }

                cep.value = json.data.cep ?? valor;
                logradouro.value = json.data.logradouro ?? '';
                bairro.value = json.data.bairro ?? '';
                cidade.value = json.data.cidade ?? '';
                estado.value = json.data.estado ?? '';

                numero.focus();
            } catch (error) {
                cep.value = valor.replace(/^(\d{5})(\d{3})$/, '$1-$2');
                limparEndereco();

                alert(error.message || 'Não foi possível consultar o CEP.');
            } finally {
                cep.disabled = false;
                cep.classList.remove('bg-slate-100');
            }
        });

        numero.addEventListener('blur', calcularEntrega);
        complemento.addEventListener('blur', calcularEntrega);

        form.addEventListener('submit', function (event) {
            if (cart.length === 0) {
                event.preventDefault();
                alert('Seu carrinho está vazio.');
                return;
            }

            if (
                tipoEntrega.value === 'entrega' &&
                (
                    !logradouro.value ||
                    !numero.value ||
                    !bairro.value ||
                    !cidade.value ||
                    !estado.value
                )
            ) {
                event.preventDefault();
                alert('Preencha o endereço completo para entrega.');
                return;
            }

            if (
                tipoEntrega.value === 'entrega' &&
                !entregaCalculada
            ) {
                event.preventDefault();
                alert('Aguarde o cálculo da taxa de entrega.');
                calcularEntrega();
                return;
            }

            inputCarrinho.value = JSON.stringify(cart);
        });

        renderCarrinho();
        atualizarBlocoEndereco();
    </script>

</body>

</html>