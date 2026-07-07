<x-rimafood.layout>
    <div class="p-8">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-4xl font-bold">🛒 Central de Pedidos</h1>

                <div class="flex items-center gap-2 text-sm text-gray-500 mt-2">
                    <span class="text-lg">⭐</span>
                    <span>Pedidos prioritários possuem atendimento preferencial</span>
                </div>
            </div>
            <a href="{{ route('restaurante.pedidos.create', $restauranteAtual->slug) }}"
                class="bg-green-500 hover:bg-green-600 text-white px-5 py-3 rounded-lg transition">
                + Novo Pedido
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-300 text-green-800 p-4 rounded-lg mb-6">
                ✅ {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow p-4 mb-6">
            <label class="block text-sm font-semibold mb-2">
                🔍 Buscar pedido
            </label>

            <input type="text" id="buscaPedido" placeholder="Digite pedido, cliente, item, status, origem..."
                class="w-full border rounded-lg p-3">
        </div>

        <div class="bg-white rounded-xl shadow">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="p-4 text-left">Pedido</th>
                        <th class="p-4 text-left">Cliente</th>
                        <th class="p-4 text-left">Itens</th>
                        <th class="p-4 text-left">Total</th>
                        <th class="p-4 text-left">Status</th>
                        <th class="p-4 text-left">Origem</th>
                        <th class="p-4 text-left">Data</th>
                        <th class="p-4 text-left">Ações</th>
                    </tr>
                </thead>

                <tbody id="tabelaPedidos">
                    @forelse($pedidos as $pedido)
                        <tr
                            class="border-b pedido-linha transition hover:bg-gray-50{{ $pedido->prioritario ? 'bg-yellow-50 border-l-4 border-yellow-400' : '' }}">
                            <td class="p-4">
                                <div class="flex items-center gap-2">
                                    @if($pedido->prioritario && !in_array($pedido->status, ['finalizado', 'cancelado']))
                                        <span class="text-yellow-500"> ⭐ </span>
                                    @endif
                                    <span class="font-bold text-g"> #{{ $pedido->id }}

                                        @if($pedido->token)
                                            <div class="text-xs text-gray-500">
                                                {{ $pedido->token }}
                                            </div>
                                        @endif
                                    </span>
                                </div>
                            </td>

                            <td class="p-4">{{ $pedido->cliente->nome }}</td>

                            <td class="p-4">
                                @foreach($pedido->itens as $item)
                                    <div class="mb-1">
                                        • {{ $item->quantidade }}x {{ $item->produto->nome }}
                                    </div>
                                @endforeach
                            </td>

                            <td class="p-4">
                                R$ {{ number_format($pedido->total, 2, ',', '.') }}
                            </td>

                            <td class="p-4 align-middle">
                                @if(in_array($pedido->status, ['cancelado', 'finalizado']))

                                    <div>

                                        @if($pedido->status === 'cancelado')

                                            <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-bold">
                                                🔴 CANCELADO
                                            </span>

                                        @else

                                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-bold">
                                                🟢 FINALIZADO
                                            </span>

                                        @endif

                                    </div>

                                @else

                                    <div class="mb-2">
                                        @php
                                            $inicioStatus = match ($pedido->status) {
                                                'novo' => $pedido->novo_em ?? $pedido->created_at,
                                                'preparando' => $pedido->preparando_em,
                                                'pronto' => $pedido->pronto_em,
                                                'saiu_entrega' => $pedido->pronto_em,
                                                default => null,
                                            };
                                        @endphp

                                        @if($inicioStatus)

                                            @php

                                                $minutos = \Carbon\Carbon::parse($inicioStatus)
                                                    ->diffInMinutes(now());

                                            @endphp

                                            @if($minutos < 20)

                                                <div class="text-xs font-semibold text-green-600 mt-1">

                                                    🟢 {{ floor($minutos) }} min

                                                </div>

                                            @elseif($minutos < 40)

                                                <div class="text-xs font-semibold text-orange-500 mt-1">

                                                    🟠 {{ floor($minutos) }} min

                                                </div>

                                            @else

                                                <div class="text-xs font-semibold text-red-600 mt-1">

                                                    🔴 {{ floor($minutos) }} min

                                                </div>

                                            @endif

                                        @endif

                                    </div>

                                    <form action="{{ route('restaurante.pedidos.status', [$restauranteAtual->slug, $pedido]) }}"
                                        method="POST">
                                        @csrf
                                        @method('PATCH')

                                        <select name="status" onchange="this.form.submit()"
                                            class="border rounded-lg px-3 py-2 bg-white">
                                            <option value="novo" @selected($pedido->status === 'novo')>
                                                🟡 Novo
                                            </option>

                                            <option value="preparando" @selected($pedido->status === 'preparando')>
                                                🔵 Preparando
                                            </option>

                                            <option value="pronto" @selected($pedido->status === 'pronto')>
                                                🟣 Pronto
                                            </option>

                                            <option value="saiu_entrega" @selected($pedido->status === 'saiu_entrega')>
                                                🚚 Saiu para entrega
                                            </option>

                                            <option value="finalizado" @selected($pedido->status === 'finalizado')>
                                                🟢 Finalizado
                                            </option>

                                            <option value="cancelado" @selected($pedido->status === 'cancelado')>
                                                🔴 Cancelado
                                            </option>
                                        </select>
                                    </form>

                                @endif
                            </td>

                            <td class="p-4">
                                @if($pedido->origem === 'balcao')
                                    🏪 Balcão
                                @elseif($pedido->origem === 'whatsapp')
                                    📱 WhatsApp
                                @else
                                    🌐 Online
                                @endif
                            </td>

                            <td class="p-4">
                                {{ $pedido->created_at->format('d/m/Y H:i') }}
                            </td>

                            <td class="p-4">
                                <div class="flex gap-3">
                                    <a href="{{ route('restaurante.pedidos.show', [$restauranteAtual->slug, $pedido]) }}"
                                        class="text-blue-500">
                                        👁️
                                    </a>

                                    <a href="{{ route('restaurante.pedidos.imprimir', [$restauranteAtual->slug, $pedido]) }}"
                                        target="_blank" class="text-gray-600 hover:text-black">
                                        🖨️
                                    </a>

                                    @if(in_array($pedido->status, ['novo', 'preparando']))

                                        <a href="{{ route('restaurante.pedidos.edit', [$restauranteAtual->slug, $pedido]) }}"
                                            class="text-yellow-500">
                                            🖊️
                                        </a>

                                        <form
                                            action="{{ route('restaurante.pedidos.cancelar', [$restauranteAtual->slug, $pedido]) }}"
                                            method="POST">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit" class="text-red-500"
                                                onclick="return confirm('Tem certeza que deseja cancelar este pedido?')">
                                                🚫
                                            </button>
                                        </form>

                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-gray-500">
                                Nenhum pedido criado ainda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div id="semResultados" class="hidden p-8 text-center text-gray-500">
                Nenhum pedido encontrado.
            </div>
        </div>

    </div>

    <script>
        const buscaPedido = document.getElementById('buscaPedido');
        const linhas = document.querySelectorAll('.pedido-linha');
        const semResultados = document.getElementById('semResultados');

        buscaPedido.addEventListener('input', function () {
            const termo = this.value.toLowerCase().trim();
            let encontrados = 0;

            linhas.forEach(function (linha) {
                const texto = linha.innerText.toLowerCase();

                if (texto.includes(termo)) {
                    linha.style.display = '';
                    encontrados++;
                } else {
                    linha.style.display = 'none';
                }
            });

            semResultados.classList.toggle('hidden', encontrados > 0);
        });
    </script>

</x-rimafood.layout>