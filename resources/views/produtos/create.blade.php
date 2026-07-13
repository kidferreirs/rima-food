<x-rimafood.layout>

    <div class="p-8 max-w-3xl">

        <h1 class="text-4xl font-bold mb-2">🍔 Novo Produto</h1>

        <p class="text-gray-500 mb-8">
            Restaurante: <strong>{{ $restaurante->nome }}</strong>
        </p>

        <form action="{{ route('restaurante.produtos.store', $restaurante->slug) }}" method="POST"
            enctype="multipart/form-data" class="space-y-4">
            @csrf

            <select name="categoria_id" class="w-full border rounded-lg p-3" required>
                <option value="">Selecione a categoria</option>

                @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}">
                        {{ $categoria->nome }}
                    </option>
                @endforeach
            </select>

            <input type="text" name="nome" placeholder="Nome do produto" class="w-full border rounded-lg p-3" required>

            <textarea name="descricao" placeholder="Descrição do produto" class="w-full border rounded-lg p-3"
                rows="4"></textarea>

            <input type="number" step="0.01" name="preco" placeholder="Preço" class="w-full border rounded-lg p-3"
                required>

            <div>
                <label class="font-semibold block mb-2">📷 Foto do produto</label>
                <input type="file" name="imagem" accept="image/*" class="w-full border rounded-lg p-3">
            </div>

            <hr class="my-8">

            <h2 class="text-2xl font-bold mb-4"> 🧠 Garçom Inteligente </h2>

            <p class="text-gray-500 mb-6"> Essas informações ajudam a IA a entender melhor o produto. </p>

 <div>
                <label class="block font-semibold mb-2">
                    🔎 Palavras-chave
                </label>

                <textarea name="palavras_chave" rows="2" placeholder="hambúrguer, burger, lanche, carne"
                    class="w-full border rounded-lg p-3"></textarea>

                <p class="text-sm text-gray-500 mt-1">
                    Termos que ajudam o cliente a encontrar este produto.
                </p>
            </div>

            <div>
                <label class="block font-semibold mb-2">
                    🔄 Sinônimos
                </label>

                <textarea name="sinonimos" rows="2" placeholder="x-burger, cheeseburger"
                    class="w-full border rounded-lg p-3"></textarea>

                <p class="text-sm text-gray-500 mt-1">
                    Outros nomes pelos quais o produto é conhecido.
                </p>
            </div>

            <div>
                <label class="block font-semibold mb-2">
                    🥬 Ingredientes
                </label>

                <textarea name="ingredientes" rows="3" placeholder="pão, carne, queijo, alface..."
                    class="w-full border rounded-lg p-3"></textarea>

                <p class="text-sm text-gray-500 mt-1">
                    Informe os principais ingredientes.
                </p>
            </div>

            <div>
                <label class="block font-semibold mb-2">
                    ⚠️ Restrições
                </label>

                <textarea name="restricoes" rows="2" placeholder="glúten, lactose"
                    class="w-full border rounded-lg p-3"></textarea>

                <p class="text-sm text-gray-500 mt-1">
                    Informe alergênicos ou restrições alimentares.
                </p>
            </div>

            @php

                $tagsSelecionadas = collect(
                    explode(',', old('tags', $produto->tags ?? ''))
                )->map(fn($tag) => trim(mb_strtolower($tag)));

                $tagsDisponiveis = [
                    'destaque',
                    'recomendado',
                    'promocao',
                    'novidade',
                    'premium',
                    'artesanal',
                    'picante',
                    'vegano',
                    'fitness',
                    'mais vendido',
                ];

            @endphp

            <div class="border rounded-2xl p-5 bg-slate-50">

                <label class="block font-bold text-lg mb-2">
                    ⭐ Tags Inteligentes
                </label>

                <p class="text-sm text-slate-500 mb-5">
                    Escolha as características deste produto.
                </p>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">

                    @foreach($tagsDisponiveis as $tag)

                        <label class="cursor-pointer rounded-xl border bg-white
                           px-4 py-3 flex items-center gap-2
                           hover:border-green-400 transition">

                            <input type="checkbox" class="rounded" name="tags[]" value="{{ $tag }}"
                                @checked($tagsSelecionadas->contains($tag))>

                            <span class="capitalize">
                                {{ str_replace('_', ' ', $tag) }}
                            </span>

                        </label>

                    @endforeach

                </div>

            </div>

            <button class="bg-green-500 text-white px-6 py-3 rounded-lg">
                Salvar
            </button>
        </form>

    </div>

</x-rimafood.layout>