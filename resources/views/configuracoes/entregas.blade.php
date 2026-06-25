<x-rimafood.layout>

    <div class="p-8 max-w-3xl">

        <h1 class="text-4xl font-bold mb-2">
            🚚 Configurações de Entrega
        </h1>

        <p class="text-gray-500 mb-6">
            Defina as taxas por distância.
        </p>

        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded-xl mb-6">
            💡 Estas taxas serão utilizadas automaticamente nos pedidos por entrega e futuramente pelo WhatsApp.
        </div>

        <div class="bg-blue-50 border border-blue-200 p-4 rounded-xl mb-6">

        @if(session('success'))
            <div class="bg-green-100 border border-green-300 text-green-700 p-4 rounded-xl mb-6">
                ✅ {{ session('success') }}
            </div>
        @endif

        <form
            action="{{ route('configuracoes.entregas.salvar') }}"
            method="POST"
            class="bg-white rounded-xl shadow p-8"
        >
            @csrf

            <h2 class="text-2xl font-bold mb-6">
                🚚 Faixas de Entrega
            </h2>

            <div class="space-y-6">

                <div>
                    <label class="font-semibold block mb-2">
                        📍 0 a 5 km
                    </label>

                    <div class="flex items-center gap-3">
                        <span class="font-bold text-gray-500">
                            R$
                        </span>

                        <input
                            type="number"
                            step="0.01"
                            name="ate_5km"
                            value="{{ $configuracao->ate_5km }}"
                            class="w-full border rounded-xl p-3"
                        >
                    </div>
                </div>

                <div class="border-t pt-6">
                    <label class="font-semibold block mb-2">
                        📍 6 a 10 km
                    </label>

                    <div class="flex items-center gap-3">
                        <span class="font-bold text-gray-500">
                            R$
                        </span>

                        <input
                            type="number"
                            step="0.01"
                            name="ate_10km"
                            value="{{ $configuracao->ate_10km }}"
                            class="w-full border rounded-xl p-3"
                        >
                    </div>
                </div>

                <div class="border-t pt-6">
                    <label class="font-semibold block mb-2">
                        📍 11 km em diante
                    </label>

                    <div class="flex items-center gap-3">
                        <span class="font-bold text-gray-500">
                            R$
                        </span>

                        <input
                            type="number"
                            step="0.01"
                            name="acima_10km"
                            value="{{ $configuracao->acima_10km }}"
                            class="w-full border rounded-xl p-3"
                        >
                    </div>
                </div>

                <button
                    class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-xl font-semibold"
                >
                    💾 Salvar
                </button>

            </div>

        </form>

    </div>

</x-rimafood.layout>