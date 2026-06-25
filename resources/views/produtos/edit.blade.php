<x-rimafood.layout>

<div class="p-8 max-w-3xl">

    <div class="flex justify-between items-center mb-8">

        <h1 class="text-4xl font-bold">
            ✏️ Editar Produto
        </h1>

        <a href="{{ route('produtos.index') }}">
            ← Voltar
        </a>

    </div>

    <form action="{{ route('produtos.update', $produto) }}" method="POST" class="space-y-4">

        @csrf
        @method('PUT')

        <select name="categoria_id" class="w-full border rounded-lg p-3">

            @foreach($categorias as $categoria)

                <option
                    value="{{ $categoria->id }}"
                    @selected($produto->categoria_id == $categoria->id)
                >

                    {{ $categoria->nome }}

                </option>

            @endforeach

        </select>

        <input
            type="text"
            name="nome"
            value="{{ $produto->nome }}"
            class="w-full border rounded-lg p-3"
        >

        <textarea
            name="descricao"
            class="w-full border rounded-lg p-3"
        >{{ $produto->descricao }}</textarea>

        <input
            type="number"
            step="0.01"
            name="preco"
            value="{{ $produto->preco }}"
            class="w-full border rounded-lg p-3"
        >

        <button class="bg-green-500 text-white px-6 py-3 rounded-lg">

            Salvar Alterações

        </button>

    </form>

</div>

</x-rimafood.layout>