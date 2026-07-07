@php
    $origemLabel = [
        'whatsapp' => '🟢 WhatsApp',
        'balcao' => '🏪 Balcão',
        'cardapio' => '🌐 Cardápio',
        'ifood' => '🔴 iFood',
        'rappi' => '🟡 Rappi',
    ][$pedido->origem] ?? ucfirst($pedido->origem);

    $minutos = (int) $pedido->created_at->diffInMinutes(now());

    $corTempo = 'text-green-600';

    if ($minutos >= 10) {
        $corTempo = 'text-yellow-600';
    }

    if ($minutos >= 20) {
        $corTempo = 'text-red-600';
    }

    $textoTempo = $minutos < 1 ? 'agora' : $minutos . ' min';
@endphp

<div class="bg-white border rounded-2xl shadow p-5 mb-4 hover:shadow-md transition">

    <div class="flex justify-between items-start mb-4">
        <div>
            <div class="text-xl font-bold">
                🍔 Pedido #{{ $pedido->id }}
            </div>

            <div class="text-gray-500 mt-1">
                👤 {{ $pedido->cliente->nome ?? 'Cliente' }}
            </div>
        </div>

        <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full">
            {{ $origemLabel }}
        </span>
    </div>

    <div class="bg-gray-50 rounded-xl p-4 mb-4">
        <div class="text-xs text-gray-500 font-semibold">
            TOTAL
        </div>

        <div class="text-3xl font-bold">
            R$ {{ number_format($pedido->total, 2, ',', '.') }}
        </div>
    </div>

    <div class="border-t pt-4 mb-4 space-y-2">
        @forelse($pedido->itens as $item)
            <div class="text-sm">
                • {{ $item->quantidade }}x {{ $item->produto->nome }}
            </div>
        @empty
            <div class="text-sm text-gray-400">
                Sem itens registrados
            </div>
        @endforelse
    </div>

    <div class="flex justify-between text-sm text-gray-500 mb-4">
        <span>🕐 {{ $pedido->created_at->format('H:i') }}</span>

        <span class="{{ $corTempo }} font-bold">
            ⏱️ {{ $textoTempo }}
        </span>
    </div>

    @if($pedido->status === 'novo')
        <form method="POST" action="{{ route('restaurante.pedidos.status', [$restauranteAtual->slug, $pedido]) }}">
            @csrf
            @method('PATCH')

            <input type="hidden" name="status" value="preparando">

            <button class="w-full bg-orange-500 hover:bg-orange-600 text-white py-3 rounded-xl font-bold">
                ▶ COMEÇAR
            </button>
        </form>
    @elseif($pedido->status === 'preparando')
        <form method="POST" action="{{ route('restaurante.pedidos.status', [$restauranteAtual->slug, $pedido]) }}">
            @csrf
            @method('PATCH')

            <input type="hidden" name="status" value="pronto">

            <button class="w-full bg-blue-500 hover:bg-blue-600 text-white py-3 rounded-xl font-bold">
                🍔 PRONTO
            </button>
        </form>
    @elseif($pedido->status === 'pronto')
        <form method="POST" action="{{ route('restaurante.pedidos.status', [$restauranteAtual->slug, $pedido]) }}">
            @csrf
            @method('PATCH')

            <input type="hidden" name="status" value="finalizado">

            <button class="w-full bg-green-500 hover:bg-green-600 text-white py-3 rounded-xl font-bold">
                🏁 FINALIZAR
            </button>
        </form>
    @endif

</div>