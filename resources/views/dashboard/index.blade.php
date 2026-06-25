<x-rimafood.layout>

    <div class="p-8">

        <div class="flex justify-between items-center mb-2">

            <h1 class="text-4xl font-bold">
                🍔 Dashboard
            </h1>

            <a href="{{ route('pedidos.create') }}"
                class="bg-green-500 hover:bg-green-600 text-white px-5 py-3 rounded-xl font-semibold shadow-sm transition">
                + Novo Pedido
            </a>

        </div>

        @if($restaurante)
            <p class="text-gray-500 mb-8">
                Bom dia! Restaurante ativo: <strong>{{ $restaurante->nome }}</strong>
            </p>
        @else
            <p class="text-gray-500 mb-8">
                Nenhum restaurante cadastrado ainda.
            </p>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

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

        @if(session('success'))
            <div class="bg-green-100 border border-green-300 text-green-800 p-4 rounded-lg mb-6">
                ✅ {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow p-6">

            <h2 class="text-2xl font-bold mb-4">🕒 Últimos Pedidos</h2>

            <table class="w-full">

                <thead>
                    <tr class="border-b">
                        <th class="p-3 text-left">Pedido</th>
                        <th class="p-3 text-left">Cliente</th>
                        <th class="p-3 text-left">Total</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Data</th>
                        <th class="p-3 text-left">Ações</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($ultimosPedidos as $pedido)

                        <tr class="border-b">

                            <td class="p-3 font-bold">
                                <button onclick="abrirModal('pedido{{ $pedido->id }}')"
                                    class="text-blue-600 hover:underline font-bold">
                                    #{{ $pedido->id }}
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
                                    🟡 Novo
                                @elseif($pedido->status === 'preparando')
                                    🔵 Preparando
                                @elseif($pedido->status === 'pronto')
                                    🟣 Pronto
                                @elseif($pedido->status === 'saiu_entrega')
                                    🚚 Saiu para entrega
                                @elseif($pedido->status === 'finalizado')
                                    🟢 Finalizado
                                @elseif($pedido->status === 'cancelado')
                                    🔴 Cancelado
                                @endif
                            </td>

                            <td class="p-3">
                                {{ $pedido->created_at->format('d/m/Y H:i') }}
                            </td>

                            <td class="p-3 text-center">

                                @if($pedido->status === 'finalizado')

                                    <a href="{{ route('pedidos.imprimir', $pedido) }}" target="_blank"
                                        class="text-xl hover:scale-110 inline-block transition" title="Imprimir pedido">
                                        🖨️
                                    </a>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="p-6 text-center text-gray-500">
                                Nenhum pedido criado ainda.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    @foreach($ultimosPedidos as $pedido)

        <div id="pedido{{ $pedido->id }}" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">

            <div class="bg-white rounded-2xl w-[600px] max-w-[95%] p-8 shadow-2xl relative">

                <button onclick="fecharModal('pedido{{ $pedido->id }}')" class="absolute top-4 right-4 text-2xl">
                    ✖
                </button>

                <h2 class="text-3xl font-bold mb-6">
                    🛒 Pedido #{{ $pedido->id }}
                </h2>

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

                        <form action="{{ route('pedidos.status', $pedido) }}" method="POST" class="mt-4">

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

                        <div class="border-b py-2">
                            {{ $item->quantidade }}x {{ $item->produto->nome }}
                        </div>

                    @endforeach

                </div>

                @if($pedido->observacao)

                    <div class="mt-6 bg-gray-100 rounded-xl p-4">
                        <strong>📝 Observação:</strong>
                        <p>{{ $pedido->observacao }}</p>
                    </div>

                @endif

            </div>

        </div>

    @endforeach

    <script>
        function abrirModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function fecharModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        setInterval(() => {

            window.location.reload();

        }, 60000);
    </script>

</x-rimafood.layout>