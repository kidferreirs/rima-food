<x-rimafood.layout>

    <div class="py-8">
        <div class="max-w-7xl mx-auto">

            <h1 class="text-4xl font-bold mb-2">
                🍳 Central da Cozinha
            </h1>

            <p class="text-gray-500 mb-8">
                Restaurante: <strong>{{ $restaurante->nome }}</strong><br>
                Acompanhe todos os pedidos em tempo real.
            </p>

            <div class="grid grid-cols-3 gap-6">

                <div class="bg-white rounded-xl shadow p-5">
                    <h2 class="text-xl font-bold text-green-600 mb-4">
                        🟢 Novos ({{ $novos->count() }})
                    </h2>

                    @foreach($novos as $pedido)
                        <x-cozinha.card-pedido :pedido="$pedido" />
                    @endforeach
                </div>

                <div class="bg-white rounded-xl shadow p-5">
                    <h2 class="text-xl font-bold text-yellow-600 mb-4">
                        🟠 Em preparo ({{ $preparo->count() }})
                    </h2>

                    @foreach($preparo as $pedido)
                        <x-cozinha.card-pedido :pedido="$pedido" />
                    @endforeach
                </div>

                <div class="bg-white rounded-xl shadow p-5">
                    <h2 class="text-xl font-bold text-blue-600 mb-4">
                        🔵 Prontos ({{ $prontos->count() }})
                    </h2>

                    @foreach($prontos as $pedido)
                        <x-cozinha.card-pedido :pedido="$pedido" />
                    @endforeach
                </div>

            </div>

        </div>
    </div>

    <script>
        setInterval(() => {
            window.location.reload();
        }, 10000);
    </script>

</x-rimafood.layout>