<x-rimafood.layout>

    <div class="p-8 max-w-3xl">

        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-4xl font-bold">✏️ Editar Categoria</h1>
                <p class="text-gray-500 mt-2">
                    Restaurante: <strong>{{ $restaurante->nome }}</strong>
                </p>
            </div>

            <a href="{{ route('restaurante.categorias.index', $restaurante->slug) }}"
                class="text-gray-600 hover:underline">
                ← Voltar
            </a>
        </div>

        <form action="{{ route('restaurante.categorias.update', [$restaurante->slug, $categoria]) }}" method="POST"
            class="space-y-4">
            @csrf
            @method('PUT')

            <input type="text" name="nome" value="{{ $categoria->nome }}" class="w-full border rounded-lg p-3" required>

            <div>
                <label class="block font-semibold mb-2">
                    Sinônimos
                </label>

                <textarea name="sinonimos" rows="3" placeholder="Ex.: doce, doces, sobremesa, sobremesas, torta"
                    class="w-full border rounded-lg p-3">{{ old('sinonimos', $categoria->sinonimos) }}</textarea>
            </div>

            <div>
                <label class="block font-semibold mb-2">
                    Palavras-chave
                </label>

                <textarea name="palavras_chave" rows="3" placeholder="Ex.: bolo, chocolate, pudim, petit gateau"
                    class="w-full border rounded-lg p-3">{{ old('palavras_chave', $categoria->palavras_chave) }}</textarea>
            </div>

            <button class="bg-green-500 text-white px-6 py-3 rounded-lg">
                Salvar Alterações
            </button>
        </form>

    </div>

</x-rimafood.layout>