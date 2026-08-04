<x-rimafood.layout>

    <div class="p-4 sm:p-6 lg:p-8">

        <div class="mb-2 flex items-center justify-between gap-4">

            <h1 class="text-3xl font-bold sm:text-4xl"> 🍔 Painel </h1>

            <a href="{{ route('restaurante.pedidos.create', $restaurante->slug) }}"
                class="inline-flex items-center justify-center whitespace-nowrap rounded-xl bg-green-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-600">
                + Novo Pedido
            </a>

        </div>

        @if($restaurante)
            <p class="text-gray-500 mb-8">
                Restaurante ativo: <strong>{{ $restaurante->nome }}</strong>
            </p>
        @else
            <p class="text-gray-500 mb-8">
                Nenhum restaurante cadastrado ainda.
            </p>
        @endif

        @if(session('success'))
            <div class="bg-green-100 border border-green-300 text-green-800 p-4 rounded-lg mb-6">
                ✅ {{ session('success') }}
            </div>
        @endif

        <div class="mb-8 rounded-2xl bg-white p-4 shadow sm:p-6">

            <div class="mb-5 flex items-center justify-between gap-4">
                <h2 class="text-xl font-bold sm:text-2xl"> 🕒 Últimos Pedidos </h2>

                <a href="{{ route('restaurante.pedidos.index', $restaurante->slug) }}"
                    class="text-sm font-semibold text-green-600 hover:text-green-700">
                    Ver todos
                </a>
            </div>

            {{-- Cards mobile --}}
            <div class="space-y-3 md:hidden">

                @forelse($ultimosPedidos as $pedido)

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

                    <button type="button" onclick="abrirModal('pedido{{ $pedido->id }}')" class=" w-full rounded-2xl border border-gray-100 bg-white p-4 text-left shadow-sm
                                    transition active:scale-[0.99]">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">

                                <p class="text-lg font-bold text-blue-600">
                                    Pedido #{{ $pedido->numero_pedido ?? $pedido->id }}
                                </p>

                                <p class="mt-1 truncate font-semibold text-gray-900">
                                    {{ $pedido->cliente->nome ?? 'Cliente não informado' }}
                                </p>

                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $pedido->created_at->format('d/m/Y H:i') }}
                                </p>

                                @if($pedido->token)
                                    <p class="mt-1 text-xs text-gray-400">
                                        Token: {{ $pedido->token }}
                                    </p>
                                @endif

                            </div>

                            <div class="flex shrink-0 flex-col items-end gap-3">
                                <p class="font-bold text-gray-900">
                                    R$ {{ number_format($pedido->total, 2, ',', '.') }}
                                </p>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </button>
                @empty

                    <div class="rounded-2xl bg-gray-50 p-6 text-center text-gray-500">
                        Nenhum pedido criado ainda.
                    </div>

                @endforelse

            </div>

            {{-- Tabela desktop --}}
            <div class="hidden overflow-x-auto md:block">

                <table class="w-full">

                    <thead>
                        <tr class="border-b">
                            <th class="p-3 text-left">Pedido</th>
                            <th class="p-3 text-left">Cliente</th>
                            <th class="p-3 text-left">Total</th>
                            <th class="p-3 text-left">Status</th>
                            <th class="p-3 text-left">Data</th>
                            <th class="p-3 text-center">Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($ultimosPedidos as $pedido)

                            <tr class="border-b">

                                <td class="p-3 font-bold">
                                    <button type="button" onclick="abrirModal('pedido{{ $pedido->id }}')"
                                        class="font-bold text-blue-600 hover:underline">
                                        #{{ $pedido->numero_pedido ?? $pedido->id }}

                                        @if($pedido->token)
                                            <span class="block text-xs font-normal text-gray-500">
                                                Token: {{ $pedido->token }}
                                            </span>
                                        @endif
                                    </button>
                                </td>

                                <td class="p-3">
                                    {{ $pedido->cliente->nome ?? 'Cliente não informado' }}
                                </td>

                                <td class="p-3">
                                    R$ {{ number_format($pedido->total, 2, ',', '.') }}
                                </td>

                                <td class="p-3">
                                    @if($pedido->status === 'novo')
                                        Novo
                                    @elseif($pedido->status === 'preparando')
                                        Preparando
                                    @elseif($pedido->status === 'pronto')
                                        Pronto
                                    @elseif($pedido->status === 'saiu_entrega')
                                        Saiu para entrega
                                    @elseif($pedido->status === 'finalizado')
                                        Finalizado
                                    @elseif($pedido->status === 'cancelado')
                                        Cancelado
                                    @endif
                                </td>

                                <td class="p-3">
                                    {{ $pedido->created_at->format('d/m/Y H:i') }}
                                </td>

                                <td class="p-3 text-center">

                                    @if($pedido->status === 'finalizado')
                                        <a href="{{ route('restaurante.pedidos.imprimir', [$restaurante->slug, $pedido]) }}"
                                            target="_blank"
                                            class="inline-block text-sm font-semibold text-blue-600 hover:underline">
                                            🖨️
                                        </a>
                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="p-6 text-center text-gray-500">
                                    Nenhum pedido criado ainda.
                                </td>
                            </tr>

                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Financeiro Hoje --}}

        <div class="bg-white rounded-xl shadow p-6 mb-8">

            <div class="flex items-center justify-between mb-6">

                <h2 class="text-2xl font-bold">

                    💰 Financeiro Hoje

                </h2>

                <span class="text-sm text-gray-500">

                    {{ now()->format('d/m/Y') }}

                </span>

            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">

                {{-- Dinheiro --}}

                <div class="bg-green-50 rounded-xl p-5 border border-green-100">

                    <h3 class="text-gray-500 text-lg mb-3">

                        💵 Dinheiro

                    </h3>

                    <p class="text-4xl font-bold">

                        R$ {{ number_format($dinheiroHoje, 2, ',', '.') }}

                    </p>

                </div>

                {{-- Cartão --}}

                <div class="bg-blue-50 rounded-xl p-5 border border-blue-100">

                    <h3 class="text-gray-500 text-lg mb-3">

                        💳 Cartão

                    </h3>

                    <p class="text-4xl font-bold">

                        R$ {{ number_format($cartaoHoje, 2, ',', '.') }}

                    </p>

                </div>

                {{-- Pix --}}

                <div class="bg-purple-50 rounded-xl p-5 border border-purple-100">

                    <h3 class="text-gray-500 text-lg mb-3">

                        🏦 Pix

                    </h3>

                    <p class="text-4xl font-bold">

                        R$ {{ number_format($pixHoje, 2, ',', '.') }}

                    </p>

                </div>

            </div>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-gray-500">🛒 Total de Pedidos</h2>
                <p class="text-4xl font-bold">{{ $totalPedidos }}</p>
            </div>

            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-gray-500">👥 Clientes</h2>
                <p class="text-4xl font-bold">{{ $clientes }}</p>
            </div>

            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-gray-500">🍔 Produtos</h2>
                <p class="text-4xl font-bold">{{ $produtos }}</p>
            </div>

            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-gray-500">📦 Pendentes</h2>
                <p class="text-4xl font-bold">{{ $pedidosPendentes }}</p>
            </div>

        </div>

    </div>

    @foreach($ultimosPedidos as $pedido)

        <div id="pedido{{ $pedido->id }}" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">

            <div class="bg-white rounded-2xl w-[600px] max-w-[95%] max-h-[90vh] overflow-y-auto p-8 shadow-2xl relative">

                <button type="button" onclick="fecharModal('pedido{{ $pedido->id }}')" class="absolute right-4 top-4 inline-flex h-10 w-10
                items-center justify-center rounded-full bg-gray-100 text-gray-600 transition hover:bg-gray-200 hover:text-gray-900" aria-label="Fechar">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <h2 class="text-3xl font-bold mb-6"> 🛒 Pedido #{{ $pedido->numero_pedido ?? $pedido->id }} </h2>

                <div class="space-y-3">

                    <p>
                        👤 Cliente:
                        <strong>{{ $pedido->cliente->nome ?? 'Cliente não informado' }}</strong>
                    </p>

                    <p>
                        💰 Total:
                        <strong>R$ {{ number_format($pedido->total, 2, ',', '.') }}</strong>
                    </p>

                    @if(in_array($pedido->status, ['cancelado', 'finalizado']))

                        <div class="mt-4 bg-gray-100 rounded-xl p-4">

                            <strong>📦 Status:</strong>

                            @if($pedido->status === 'cancelado')
                                🔴 Cancelado

                                <p class="text-sm text-gray-500 mt-2">
                                    Este pedido não pode mais ter o status alterado.
                                </p>
                            @else
                                🟢 Finalizado

                                <p class="text-sm text-gray-500 mt-2">
                                    Este pedido foi finalizado e já pode ser impresso.
                                </p>

                            @endif

                        </div>

                    @else

                        <form action="{{ route('restaurante.pedidos.status', [$restaurante->slug, $pedido]) }}" method="POST"
                            class="mt-4">

                            <input type="hidden" name="origem" value="dashboard">
                            @csrf
                            @method('PATCH')

                            <label class="block font-bold mb-2">
                                📦 Alterar Status
                            </label>

                            <select name="status" class="w-full border rounded-xl p-3 mb-4">

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

                            <button type="submit"
                                class="bg-green-500 hover:bg-green-600 text-white px-5 py-3 rounded-xl font-semibold">
                                Salvar Status
                            </button>

                        </form>

                    @endif

                    <p>
                        📅 Data:
                        <strong>{{ $pedido->created_at->format('d/m/Y H:i') }}</strong>
                    </p>

                </div>

                <div class="mt-6">

                    <h3 class="font-bold mb-3">🍔 Itens</h3>

                    @foreach($pedido->itens as $item)
                        <div class="border-b py-3">
                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium">
                                    {{ $item->quantidade }}x {{ $item->produto->nome }}
                                </span>

                                <span class="font-semibold whitespace-nowrap">
                                    R$ {{ number_format($item->preco_unitario * $item->quantidade, 2, ',', '.') }}
                                </span>
                            </div>

                            @if($item->observacao)
                                <div class="mt-2 bg-gray-50 border rounded-lg p-3 text-sm text-gray-700 whitespace-pre-line">
                                    {{ $item->observacao }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($pedido->observacao)

                    <div class="mt-6 bg-gray-100 rounded-xl p-4">
                        <strong>📝 Observação:</strong>
                        <p>{{ $pedido->observacao }}</p>
                    </div>

                @endif


                @if($pedido->status === 'finalizado')

                    <div class="mt-6 border-t border-gray-200 pt-5">
                        <a href="{{ route('restaurante.pedidos.imprimir', [$restaurante->slug, $pedido]) }}" target="_blank"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gray-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-gray-800">
                            🖨️ Imprimir pedido
                        </a>
                    </div>

                @endif

            </div>

        </div>

    @endforeach

    <audio id="novoPedidoAudio" preload="auto">
        <source src="{{ asset('novo-pedido.mp3') }}" type="audio/mpeg">
    </audio>

    <script>
        function abrirModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function fecharModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        @if($pedidosPendentes > 0)
            document.addEventListener('DOMContentLoaded', () => {
                const tocou = sessionStorage.getItem('rima-pedido-tocou');
                const audio = document.getElementById('novoPedidoAudio');

                if (!tocou && audio) {
                    audio.play().catch(() => {
                        console.log('Áudio bloqueado pelo navegador até interação do usuário.');
                    });

                    sessionStorage.setItem('rima-pedido-tocou', '1');
                }
            });
        @endif

        @if($pedidosPendentes == 0)
            sessionStorage.removeItem('rima-pedido-tocou');
        @endif

        setInterval(() => {
            window.location.reload();
        }, 30000);
    </script>

</x-rimafood.layout>