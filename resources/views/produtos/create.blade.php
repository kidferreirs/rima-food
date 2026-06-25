<x-rimafood.layout>

<div class="p-8 max-w-3xl">

    <h1 class="text-4xl font-bold mb-8">🍔 Novo Produto</h1>

    <form action="{{ route('produtos.store') }}" method="POST" class="space-y-4">
        @csrf

        <select name="categoria_id" class="w-full border rounded-lg p-3" required>
            <option value="">Selecione a categoria</option>

            @foreach($categorias as $categoria)
                <option value="{{ $categoria->id }}">
                    {{ $categoria->restaurante->nome }} - {{ $categoria->nome }}
                </option>
            @endforeach
        </select>

        <input
            type="text"
            name="nome"
            placeholder="Nome do produto"
            class="w-full border rounded-lg p-3"
            required
        >

        <textarea
            name="descricao"
            placeholder="Descrição do produto"
            class="w-full border rounded-lg p-3"
            rows="4"
        ></textarea>

        <input
            type="number"
            step="0.01"
            name="preco"
            placeholder="Preço"
            class="w-full border rounded-lg p-3"
            required
        >

        <button class="bg-green-500 text-white px-6 py-3 rounded-lg">
            Salvar
        </button>
    </form>

</div>

</x-rimafood.layout>