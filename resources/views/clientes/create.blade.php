<x-rimafood.layout>

    <div class="p-8 max-w-3xl">

        <h1 class="text-4xl font-bold mb-2">👥 Novo Cliente</h1>

        <p class="text-gray-500 mb-8">
            Restaurante: <strong>{{ $restaurante->nome }}</strong>
        </p>

        @if(session('error'))
            <div class="bg-red-100 border border-red-300 text-red-800 p-4 rounded-lg mb-6">
                🔴 {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('restaurante.clientes.store', $restaurante->slug) }}" method="POST" class="space-y-4">
            @csrf

            <input
                type="text"
                name="nome"
                placeholder="Nome do cliente"
                class="w-full border rounded-lg p-3"
                required
            >

            <input
                type="text"
                name="telefone"
                placeholder="(xx) xxxx-xxxx"
                class="w-full border rounded-lg p-3"
                required
            >

            <input
                type="email"
                name="email"
                placeholder="Email"
                class="w-full border rounded-lg p-3"
            >

            <textarea
                name="observacao"
                placeholder="Observação"
                class="w-full border rounded-lg p-3"
                rows="4"
            ></textarea>

            <button class="bg-green-500 text-white px-6 py-3 rounded-lg">
                Salvar
            </button>
        </form>

    </div>

</x-rimafood.layout>