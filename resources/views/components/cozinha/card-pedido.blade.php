@php
    $origemLabel = [
        'whatsapp' => 'WhatsApp',
        'balcao' => 'Balcão',
        'cardapio' => 'Cardápio',
        'ifood' => 'iFood',
        'rappi' => 'Rappi',
    ][$pedido->origem] ?? ucfirst($pedido->origem);

    $origemClass = [
        'whatsapp' => 'bg-green-100 text-green-700',
        'balcao' => 'bg-gray-100 text-gray-700',
        'cardapio' => 'bg-blue-100 text-blue-700',
        'ifood' => 'bg-red-100 text-red-700',
        'rappi' => 'bg-orange-100 text-orange-700',
    ][$pedido->origem] ?? 'bg-gray-100 text-gray-700';

    $inicioStatus = match ($pedido->status) {
        'novo' => $pedido->novo_em ?? $pedido->created_at,
        'preparando' => $pedido->preparando_em ?? $pedido->created_at,
        'pronto' => $pedido->pronto_em ?? $pedido->created_at,
        default => $pedido->created_at,
    };

    $minutos = (int) floor(
        \Carbon\Carbon::parse($inicioStatus)->diffInMinutes(now())
    );

    $textoTempo = $minutos < 1
        ? 'Agora'
        : $minutos . ' min';

    $tempoClass = $minutos < 10
        ? 'bg-green-100 text-green-700'
        : ($minutos < 20
            ? 'bg-orange-100 text-orange-700'
            : 'bg-red-100 text-red-700');

    $statusLabel = match ($pedido->status) {
        'novo' => 'Novo',
        'preparando' => 'Em preparo',
        'pronto' => 'Pronto',
        default => ucfirst($pedido->status),
    };

    $statusClass = match ($pedido->status) {
        'novo' => 'bg-green-100 text-green-700',
        'preparando' => 'bg-orange-100 text-orange-700',
        'pronto' => 'bg-blue-100 text-blue-700',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp

<article
    class="
        rounded-2xl border bg-white p-4 shadow-sm
        transition hover:shadow-md
        {{ $pedido->prioritario
            ? 'border-yellow-300 ring-1 ring-yellow-200'
            : 'border-gray-200' }}
    "
>

    <div class="flex items-start justify-between gap-4">

        <div class="min-w-0">

            <div class="flex flex-wrap items-center gap-2">

                <h3 class="text-lg font-bold text-gray-900">
                    Pedido #{{ $pedido->numero_pedido ?? $pedido->id }}
                </h3>

                @if($pedido->prioritario)
                    <span
                        class="
                            rounded-full bg-yellow-100
                            px-2.5 py-1 text-[11px]
                            font-bold text-yellow-800
                        "
                    >
                        Prioritário
                    </span>
                @endif

            </div>

            <p class="mt-1 truncate text-sm font-medium text-gray-600">
                {{ $pedido->cliente->nome ?? 'Cliente' }}
            </p>

            @if($pedido->token)
                <p class="mt-1 text-xs text-gray-400">
                    Token: {{ $pedido->token }}
                </p>
            @endif

        </div>

        <span
            class="
                shrink-0 rounded-full px-3 py-1
                text-xs font-semibold {{ $origemClass }}
            "
        >
            {{ $origemLabel }}
        </span>

    </div>

    <div class="mt-4 flex flex-wrap gap-2">

        <span
            class="
                rounded-full px-3 py-1
                text-xs font-semibold {{ $statusClass }}
            "
        >
            {{ $statusLabel }}
        </span>

        <span
            class="
                rounded-full px-3 py-1
                text-xs font-semibold {{ $tempoClass }}
            "
        >
            {{ $textoTempo }}
        </span>

        <span
            class="
                rounded-full bg-gray-100
                px-3 py-1 text-xs font-semibold
                text-gray-700
            "
        >
            {{ $pedido->created_at->format('H:i') }}
        </span>

    </div>

    <div class="mt-4 rounded-xl bg-gray-50 p-3">

        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">
            Itens
        </p>

        <div class="mt-2 space-y-2">

            @forelse($pedido->itens as $item)

                <div class="border-b border-gray-200 pb-2 last:border-0 last:pb-0">

                    <div class="flex items-start justify-between gap-3">

                        <p class="font-semibold text-gray-900">
                            {{ $item->quantidade }}x {{ $item->produto->nome }}
                        </p>

                        <span class="shrink-0 text-sm text-gray-500">
                            R$ {{ number_format(
                                $item->preco_unitario * $item->quantidade,
                                2,
                                ',',
                                '.'
                            ) }}
                        </span>

                    </div>

                    @if($item->observacao)
                        <div
                            class="
                                mt-2 whitespace-pre-line
                                rounded-lg bg-white p-2
                                text-xs text-gray-600
                            "
                        >
                            {{ $item->observacao }}
                        </div>
                    @endif

                </div>

            @empty

                <p class="text-sm text-gray-400">
                    Sem itens registrados.
                </p>

            @endforelse

        </div>

    </div>

    <div
        class="
            mt-4 flex items-center justify-between
            border-t border-gray-100 pt-4
        "
    >

        <span class="text-sm font-medium text-gray-500">
            Total
        </span>

        <span class="text-xl font-bold text-gray-900">
            R$ {{ number_format($pedido->total, 2, ',', '.') }}
        </span>

    </div>

    <div class="mt-4">

        @if($pedido->status === 'novo')

            <form
                method="POST"
                action="{{ route(
                    'restaurante.pedidos.status',
                    [$restauranteAtual->slug, $pedido]
                ) }}"
            >
                @csrf
                @method('PATCH')

                <input
                    type="hidden"
                    name="status"
                    value="preparando"
                >

                <button
                    type="submit"
                    class="
                        w-full rounded-xl bg-orange-500
                        px-4 py-3 text-sm font-bold text-white
                        transition hover:bg-orange-600
                    "
                >
                    Iniciar preparo
                </button>
            </form>

        @elseif($pedido->status === 'preparando')

            <form
                method="POST"
                action="{{ route(
                    'restaurante.pedidos.status',
                    [$restauranteAtual->slug, $pedido]
                ) }}"
            >
                @csrf
                @method('PATCH')

                <input
                    type="hidden"
                    name="status"
                    value="pronto"
                >

                <button
                    type="submit"
                    class="
                        w-full rounded-xl bg-blue-500
                        px-4 py-3 text-sm font-bold text-white
                        transition hover:bg-blue-600
                    "
                >
                    Marcar como pronto
                </button>
            </form>

        @elseif($pedido->status === 'pronto')

            <form
                method="POST"
                action="{{ route(
                    'restaurante.pedidos.status',
                    [$restauranteAtual->slug, $pedido]
                ) }}"
            >
                @csrf
                @method('PATCH')

                <input
                    type="hidden"
                    name="status"
                    value="finalizado"
                >

                <button
                    type="submit"
                    class="
                        w-full rounded-xl bg-green-500
                        px-4 py-3 text-sm font-bold text-white
                        transition hover:bg-green-600
                    "
                >
                    Finalizar pedido
                </button>
            </form>

        @endif

    </div>

</article>