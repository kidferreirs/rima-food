<x-rimafood.layout>

<div class="p-8 max-w-3xl">

    <h1 class="text-4xl font-bold mb-8">📂 Nova Categoria</h1>

    <form action="{{ route('categorias.store') }}" method="POST" class="space-y-4">
        @csrf

        <select name="restaurante_id" class="w-full border rounded-lg p-3" required>
            <option value="">Selecione o restaurante</option>

            @foreach($restaurantes as $restaurante)
                <option value="{{ $restaurante->id }}">
                    {{ $restaurante->nome }}
                </option>
            @endforeach
        </select>

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