<x-rimafood.layout>

<div class="p-8 max-w-3xl">

    <div class="flex justify-between items-center mb-8">
        <h1 class="text-4xl font-bold">✏️ Editar Categoria</h1>

        <a href="{{ route('categorias.index') }}" class="text-gray-600 hover:underline">
            ← Voltar
        </a>
    </div>

    <form action="{{ route('categorias.update', $categoria) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <select name="restaurante_id" class="w-full border rounded-lg p-3" required>
            @foreach($restaurantes as $restaurante)
                <option value="{{ $restaurante->id }}" @selected($categoria->restaurante_id == $restaurante->id)>
                    {{ $restaurante->nome }}
                </option>
            @endforeach
        </select>

        <input
            type="text"
            name="nome"
            value="{{ $categoria->nome }}"
            class="w-full border rounded-lg p-3"
            required
        >

        <button class="bg-green-500 text-white px-6 py-3 rounded-lg">
            Salvar Alterações
        </button>
    </form>

</div>

</x-rimafood.layout>