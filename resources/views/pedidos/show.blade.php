<x-rimafood.layout>

    <div class="p-8 max-w-5xl">

        <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold">
                👁️ Pedido #{{ $pedido->id }}
                @if($pedido->prioritario)
                    <span class="text-yellow-500">⭐</span>
                @endif
            </h1>

            <a href="{{ route('restaurante.pedidos.index', $restauranteAtual->slug) }}" class="text-gray-600 hover:underline">
                ← Voltar para pedidos
            </a>
        </div>

        <div class="bg-white rounded-xl shadow p-6 mb-6">

            <h2 class="text-2xl font-bold mb-6">
                📦 Dados do Pedido
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <p class="text-gray-500 text-sm">🏪 Restaurante</p>
                    <p class="font-semibold">{{ $pedido->restaurante->nome }}</p>
                </div>

                <div>
                    <p class="text-gray-500 text-sm">📅 Data</p>
                    <p class="font-semibold">{{ $pedido->created_at->format('d/m/Y H:i') }}</p>
                </div>

                <div>
                    <p class="text-gray-500 text-sm">👤 Cliente</p>
                    <p class="font-semibold">{{ $pedido->cliente->nome }}</p>
                </div>

                @if($pedido->cliente->telefone && $pedido->cliente->telefone !== '00000000000')
                    <div>
                        <p class="text-gray-500 text-sm">📞 Telefone</p>
                        <p class="font-semibold">{{ $pedido->cliente->telefone }}</p>
                    </div>
                @endif

                <div>
                    <p class="text-gray-500 text-sm">📦 Status</p>

                    @if($pedido->status === 'novo')
                        <p class="font-semibold text-yellow-700">🟡 Novo</p>
                    @elseif($pedido->status === 'preparando')
                        <p class="font-semibold text-blue-700">🔵 Preparando</p>
                    @elseif($pedido->status === 'pronto')
                        <p class="font-semibold text-purple-700">🟣 Pronto</p>
                    @elseif($pedido->status === 'saiu_entrega')
                        <p class="font-semibold text-orange-700">🚚 Saiu para entrega</p>
                    @elseif($pedido->status === 'finalizado')
                        <p class="font-semibold text-green-700">🟢 Finalizado</p>
                    @elseif($pedido->status === 'cancelado')
                        <p class="font-semibold text-red-700">🔴 Cancelado</p>
                    @endif
                </div>

                <div>
                    <p class="text-gray-500 text-sm">💳 Pagamento</p>

                    @if($pedido->forma_pagamento === 'dinheiro')
                        <p class="font-semibold">💵 Dinheiro</p>
                    @elseif($pedido->forma_pagamento === 'cartao')
                        <p class="font-semibold">💳 Cartão</p>
                    @elseif($pedido->forma_pagamento === 'pix')
                        <p class="font-semibold">🏦 Pix</p>
                    @else
                        <p class="font-semibold">Não informado</p>
                    @endif
                </div>

                @if($pedido->prioritario)
                    <div class="md:col-span-2 bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                        <p class="font-semibold text-yellow-800">
                            ⭐ Pedido prioritário
                        </p>
                    </div>
                @endif

            </div>

            <div class="mt-6">
                <p class="text-gray-500 text-sm">📝 Observação</p>

                @if($pedido->observacao)
                    <p class="font-semibold">{{ $pedido->observacao }}</p>
                @else
                    <p class="text-gray-400">Sem observações.</p>
                @endif
            </div>

        </div>

        <div class="bg-white rounded-xl shadow p-6">

            <h2 class="text-2xl font-bold mb-4">
                🍔 Itens do Pedido
            </h2>

            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="p-3 text-left">Produto</th>
                        <th class="p-3 text-left">Qtd</th>
                        <th class="p-3 text-left">Preço Unitário</th>
                        <th class="p-3 text-left">Subtotal</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($pedido->itens as $item)
                        <tr class="border-b">
                            <td class="p-3">{{ $item->produto->nome }}</td>
                            <td class="p-3">{{ $item->quantidade }}</td>
                            <td class="p-3">
                                R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}
                            </td>
                            <td class="p-3">
                                R$ {{ number_format($item->preco_unitario * $item->quantidade, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-6 flex justify-end">

                <div class="bg-gray-100 rounded-xl p-5 min-w-[260px] text-right">

                    <p class="text-gray-500 text-sm">
                        💰 Total do Pedido
                    </p>

                    <p class="text-3xl font-bold">
                        R$ {{ number_format($pedido->total, 2, ',', '.') }}
                    </p>

                </div>

            </div>

        </div>

    </div>

</x-rimafood.layout>