<x-rimafood.layout>

    <div class="mx-auto max-w-5xl p-4 sm:p-6 lg:p-8">

        {{-- Cabeçalho compacto --}}
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">

            <div class="min-w-0">

                <h1 class="text-2xl font-bold sm:text-3xl">
                    Pedido #{{ $pedido->numero_pedido ?? $pedido->id }}
                </h1>

                <div class="mt-3 flex flex-wrap gap-2">

                    @if($pedido->token)
                        <span
                            class="
                                rounded-full bg-yellow-100
                                px-3 py-1 text-sm font-semibold
                                text-yellow-800
                            "
                        >
                            {{ $pedido->token }}
                        </span>
                    @endif

                    <span
                        class="
                            rounded-full bg-blue-100
                            px-3 py-1 text-sm font-semibold
                            text-blue-800
                        "
                    >
                        {{ ucfirst($pedido->origem) }}
                    </span>

                </div>

            </div>

            <a
                href="{{ route(
                    'restaurante.pedidos.index',
                    $restauranteAtual->slug
                ) }}"
                class="
                    whitespace-nowrap text-sm
                    font-medium text-gray-500
                    transition hover:text-gray-800
                "
            >
                ← Voltar
            </a>

        </div>

        {{-- Dados do pedido --}}
        <div class="mb-6 rounded-2xl bg-white p-5 shadow sm:p-6">

            <h2 class="mb-5 text-xl font-bold sm:text-2xl">
                Dados do Pedido
            </h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                <div>
                    <p class="text-sm text-gray-500">
                        Restaurante
                    </p>

                    <p class="font-semibold">
                        {{ $pedido->restaurante->nome }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">
                        Data
                    </p>

                    <p class="font-semibold">
                        {{ $pedido->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500">
                        Cliente
                    </p>

                    <p class="font-semibold">
                        {{ $pedido->cliente->nome }}
                    </p>
                </div>

                @if(
                    $pedido->cliente->telefone
                    && $pedido->cliente->telefone !== '00000000000'
                )
                    <div>
                        <p class="text-sm text-gray-500">
                            Telefone
                        </p>

                        <p class="font-semibold">
                            {{ $pedido->cliente->telefone }}
                        </p>
                    </div>
                @endif

                <div>
                    <p class="text-sm text-gray-500">
                        Status
                    </p>

                    @php
                        $statusLabel = match ($pedido->status) {
                            'novo' => 'Novo',
                            'preparando' => 'Preparando',
                            'pronto' => 'Pronto',
                            'saiu_entrega' => 'Saiu para entrega',
                            'finalizado' => 'Finalizado',
                            'cancelado' => 'Cancelado',
                            default => ucfirst($pedido->status),
                        };

                        $statusClass = match ($pedido->status) {
                            'novo' => 'bg-yellow-100 text-yellow-800',
                            'preparando' => 'bg-blue-100 text-blue-800',
                            'pronto' => 'bg-purple-100 text-purple-800',
                            'saiu_entrega' => 'bg-orange-100 text-orange-800',
                            'finalizado' => 'bg-green-100 text-green-800',
                            'cancelado' => 'bg-red-100 text-red-800',
                            default => 'bg-gray-100 text-gray-700',
                        };
                    @endphp

                    <span
                        class="
                            mt-1 inline-flex rounded-full
                            px-3 py-1 text-xs font-semibold
                            {{ $statusClass }}
                        "
                    >
                        {{ $statusLabel }}
                    </span>
                </div>

                <div>
                    <p class="text-sm text-gray-500">
                        Pagamento
                    </p>

                    <p class="font-semibold">
                        @if($pedido->forma_pagamento === 'dinheiro')
                            Dinheiro
                        @elseif($pedido->forma_pagamento === 'credito')
                            Cartão de Crédito
                        @elseif($pedido->forma_pagamento === 'debito')
                            Cartão de Débito
                        @elseif($pedido->forma_pagamento === 'cartao')
                            Cartão
                        @elseif($pedido->forma_pagamento === 'pix')
                            Pix
                        @else
                            Não informado
                        @endif
                    </p>
                </div>

                @if($pedido->prioritario)
                    <div
                        class="
                            rounded-xl border border-yellow-200
                            bg-yellow-50 p-4 md:col-span-2
                        "
                    >
                        <p class="font-semibold text-yellow-800">
                            Pedido prioritário
                        </p>
                    </div>
                @endif

            </div>

            <div class="mt-5 border-t border-gray-100 pt-5">

                <p class="text-sm text-gray-500">
                    Observação
                </p>

                @if($pedido->observacao)
                    <p class="mt-1 font-medium text-gray-800">
                        {{ $pedido->observacao }}
                    </p>
                @else
                    <p class="mt-1 text-gray-400">
                        Sem observações.
                    </p>
                @endif

            </div>

        </div>

        {{-- Itens do pedido --}}
        <div class="rounded-2xl bg-white p-5 shadow sm:p-6">

            <h2 class="mb-5 text-xl font-bold sm:text-2xl">
                Itens do Pedido
            </h2>

            {{-- Cards mobile --}}
            <div class="space-y-4 md:hidden">

                @foreach($pedido->itens as $item)

                    <div class="rounded-2xl border border-gray-200 p-4">

                        <div class="flex items-start justify-between gap-4">

                            <div class="min-w-0">

                                <h3 class="truncate text-lg font-bold text-gray-900">
                                    {{ $item->produto->nome }}
                                </h3>

                                <p class="mt-1 text-sm text-gray-500">
                                    Quantidade:
                                    <strong class="text-gray-800">
                                        {{ $item->quantidade }}
                                    </strong>
                                </p>

                            </div>

                            <div class="shrink-0 text-right">

                                <p class="text-xs text-gray-500">
                                    Subtotal
                                </p>

                                <p class="font-bold text-gray-900">
                                    R$ {{ number_format(
                                        $item->preco_unitario * $item->quantidade,
                                        2,
                                        ',',
                                        '.'
                                    ) }}
                                </p>

                            </div>

                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3">

                            <div class="rounded-xl bg-gray-50 p-3">
                                <p class="text-xs text-gray-500">
                                    Preço unitário
                                </p>

                                <p class="mt-1 font-semibold text-gray-900">
                                    R$ {{ number_format(
                                        $item->preco_unitario,
                                        2,
                                        ',',
                                        '.'
                                    ) }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-gray-50 p-3">
                                <p class="text-xs text-gray-500">
                                    Quantidade
                                </p>

                                <p class="mt-1 font-semibold text-gray-900">
                                    {{ $item->quantidade }}
                                </p>
                            </div>

                        </div>

                        @if($item->observacao)

                            <div
                                class="
                                    mt-4 whitespace-pre-line
                                    rounded-xl border border-gray-100
                                    bg-gray-50 p-3
                                    text-sm text-gray-700
                                "
                            >
                                {{ $item->observacao }}
                            </div>

                        @endif

                    </div>

                @endforeach

            </div>

            {{-- Tabela desktop --}}
            <div class="hidden overflow-x-auto md:block">

                <table class="w-full">

                    <thead>
                        <tr class="border-b">
                            <th class="p-3 text-left">
                                Produto
                            </th>

                            <th class="p-3 text-left">
                                Qtd
                            </th>

                            <th class="p-3 text-left">
                                Preço Unitário
                            </th>

                            <th class="p-3 text-left">
                                Subtotal
                            </th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($pedido->itens as $item)

                            <tr class="border-b">

                                <td class="p-3 align-top">

                                    <div class="font-semibold">
                                        {{ $item->produto->nome }}
                                    </div>

                                    @if($item->observacao)
                                        <div
                                            class="
                                                mt-2 whitespace-pre-line
                                                rounded-lg border bg-gray-50
                                                p-3 text-sm text-gray-700
                                            "
                                        >
                                            {{ $item->observacao }}
                                        </div>
                                    @endif

                                </td>

                                <td class="p-3 align-top">
                                    {{ $item->quantidade }}
                                </td>

                                <td class="p-3 align-top">
                                    R$ {{ number_format(
                                        $item->preco_unitario,
                                        2,
                                        ',',
                                        '.'
                                    ) }}
                                </td>

                                <td class="p-3 align-top">
                                    R$ {{ number_format(
                                        $item->preco_unitario * $item->quantidade,
                                        2,
                                        ',',
                                        '.'
                                    ) }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            {{-- Total --}}
            <div class="mt-6 flex justify-end">

                <div
                    class="
                        flex w-full items-center justify-between
                        rounded-2xl border border-green-200
                        bg-green-50 p-5 sm:w-auto sm:min-w-[320px]
                    "
                >

                    <span class="font-semibold text-gray-700">
                        Total do Pedido
                    </span>

                    <span class="text-2xl font-bold text-green-700 sm:text-3xl">
                        R$ {{ number_format($pedido->total, 2, ',', '.') }}
                    </span>

                </div>

            </div>

        </div>

        {{-- Ações --}}
        <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2">

            <a
                href="{{ route(
                    'restaurante.pedidos.imprimir',
                    [$restauranteAtual->slug, $pedido]
                ) }}"
                target="_blank"
                class="
                    inline-flex items-center justify-center
                    rounded-xl border border-gray-200
                    bg-white px-5 py-3
                    text-sm font-semibold text-gray-700
                    shadow-sm transition hover:bg-gray-50
                "
            >
                Imprimir pedido
            </a>

            @if(in_array($pedido->status, ['novo', 'preparando']))

                <a
                    href="{{ route(
                        'restaurante.pedidos.edit',
                        [$restauranteAtual->slug, $pedido]
                    ) }}"
                    class="
                        inline-flex items-center justify-center
                        rounded-xl bg-green-500
                        px-5 py-3
                        text-sm font-semibold text-white
                        shadow-sm transition hover:bg-green-600
                    "
                >
                    Editar pedido
                </a>

            @else

                <a
                    href="{{ route(
                        'restaurante.pedidos.index',
                        $restauranteAtual->slug
                    ) }}"
                    class="
                        inline-flex items-center justify-center
                        rounded-xl bg-gray-900
                        px-5 py-3
                        text-sm font-semibold text-white
                        shadow-sm transition hover:bg-gray-800
                    "
                >
                    Voltar para pedidos
                </a>

            @endif

        </div>

    </div>

</x-rimafood.layout>