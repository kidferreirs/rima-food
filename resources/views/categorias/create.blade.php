<x-rimafood.layout>

<div class="p-8 max-w-3xl">

    <h1 class="text-4xl font-bold mb-2">📂 Nova Categoria</h1>

    <p class="text-gray-500 mb-8">
        Restaurante: <strong>{{ $restaurante->nome }}</strong>
    </p>

    <form action="{{ route('restaurante.categorias.store', $restaurante->slug) }}" method="POST" class="space-y-4">
        @csrf

        <input
            type="text"
            name="nome"
            placeholder="Nome da categoria"
            class="w-full border rounded-lg p-3"
            required
        >

        <button class="bg-green-500 text-white px-6 py-3 rounded-lg">
            Salvar
        </button>
    </form>

</div>

</x-rimafood.layout>