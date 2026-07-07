<x-rimafood.layout>

    <div class="p-8 max-w-3xl">

        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-4xl font-bold">
                    ✏️ Editar Produto
                </h1>

                <p class="text-gray-500 mt-2">
                    Restaurante: <strong>{{ $restaurante->nome }}</strong>
                </p>
            </div>

            <a href="{{ route('restaurante.produtos.index', $restaurante->slug) }}"
                class="text-gray-600 hover:underline">
                ← Voltar
            </a>
        </div>

        <form action="{{ route('restaurante.produtos.update', [$restaurante->slug, $produto]) }}" method="POST"
            enctype="multipart/form-data" class="space-y-4">

            @csrf
            @method('PUT')

            <select name="categoria_id" class="w-full border rounded-lg p-3" required>
                @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}" @selected($produto->categoria_id == $categoria->id)>
                        {{ $categoria->nome }}
                    </option>
                @endforeach
            </select>

            <input type="text" name="nome" value="{{ $produto->nome }}" class="w-full border rounded-lg p-3" required>

            <textarea name="descricao" class="w-full border rounded-lg p-3"
                rows="4">{{ $produto->descricao }}</textarea>

            <input type="number" step="0.01" name="preco" value="{{ $produto->preco }}"
                class="w-full border rounded-lg p-3" required>

            @if($produto->imagem)
                <div>
                    <label class="font-semibold block mb-2">Foto atual</label>
                    <img src="{{ Storage::url($produto->imagem) }}" class="w-40 h-32 object-cover rounded-xl border">
                </div>
            @endif

            <div>
                <label class="font-semibold block mb-2">📷 Alterar foto do produto</label>
                <input type="file" name="imagem" accept="image/*" class="w-full border rounded-lg p-3">
            </div>

            <button class="bg-green-500 text-white px-6 py-3 rounded-lg">
                Salvar Alterações
            </button>

        </form>

    </div>

</x-rimafood.layout>