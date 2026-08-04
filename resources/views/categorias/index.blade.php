<x-rimafood.layout>

    <div class="p-4 sm:p-6 lg:p-8">

        {{-- Cabeçalho --}}
        <div class="mb-6 flex items-start justify-between gap-4 sm:mb-8">

            <div class="min-w-0">

                <h1 class="text-3xl font-bold sm:text-4xl">
                    Categorias
                </h1>

                <p class="mt-2 text-sm text-gray-500 sm:text-base">
                    Gerencie as categorias do cardápio.
                </p>

                <p class="mt-1 truncate text-xs text-gray-400 sm:text-sm">
                    {{ $restauranteAtual->nome }}
                </p>

            </div>

            <a
                href="{{ route(
                    'restaurante.categorias.create',
                    $restauranteAtual->slug
                ) }}"
                class="
                    inline-flex shrink-0 items-center justify-center
                    whitespace-nowrap rounded-xl
                    bg-green-500 px-4 py-2
                    text-sm font-semibold text-white
                    shadow-sm transition
                    hover:bg-green-600
                "
            >
                + Categoria
            </a>

        </div>

        @if(session('success'))
            <div
                class="
                    mb-6 rounded-xl
                    border border-green-200
                    bg-green-50 p-4
                    text-sm text-green-800
                "
            >
                {{ session('success') }}
            </div>
        @endif

        {{-- Cards mobile --}}
        <div class="space-y-3 md:hidden">

            @forelse($categorias as $categoria)

                <div
                    class="
                        rounded-2xl border border-gray-100
                        bg-white p-4 shadow-sm
                    "
                >

                    <div class="flex items-start justify-between gap-4">

                        <div class="min-w-0">

                            <h2 class="truncate text-lg font-bold text-gray-900">
                                {{ $categoria->nome }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-500">
                                {{ $categoria->produtos_count }}
                                {{ $categoria->produtos_count === 1
                                    ? 'produto'
                                    : 'produtos' }}
                            </p>

                        </div>

                        @if($categoria->ativo)
                            <span
                                class="
                                    shrink-0 rounded-full
                                    bg-green-100 px-3 py-1
                                    text-xs font-semibold text-green-700
                                "
                            >
                                Ativa
                            </span>
                        @else
                            <span
                                class="
                                    shrink-0 rounded-full
                                    bg-gray-100 px-3 py-1
                                    text-xs font-semibold text-gray-600
                                "
                            >
                                Inativa
                            </span>
                        @endif

                    </div>

                    <div
                        class="
                            mt-4 grid grid-cols-2 gap-3
                            border-t border-gray-100 pt-4
                        "
                    >

                        <a
                            href="{{ route(
                                'restaurante.categorias.edit',
                                [
                                    $restauranteAtual->slug,
                                    $categoria
                                ]
                            ) }}"
                            class="
                                inline-flex items-center justify-center
                                rounded-xl border border-gray-200
                                px-4 py-2.5
                                text-sm font-semibold text-gray-700
                                transition hover:bg-gray-50
                            "
                        >
                            Editar
                        </a>

                        <form
                            action="{{ route(
                                'restaurante.categorias.status',
                                [
                                    $restauranteAtual->slug,
                                    $categoria
                                ]
                            ) }}"
                            method="POST"
                        >
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="
                                    inline-flex w-full items-center justify-center
                                    rounded-xl px-4 py-2.5
                                    text-sm font-semibold transition

                                    @if($categoria->ativo)
                                        bg-red-50 text-red-700
                                        hover:bg-red-100
                                    @else
                                        bg-green-50 text-green-700
                                        hover:bg-green-100
                                    @endif
                                "
                            >
                                {{ $categoria->ativo
                                    ? 'Desativar'
                                    : 'Ativar' }}
                            </button>

                        </form>

                    </div>

                </div>

            @empty

                <div
                    class="
                        rounded-2xl bg-white
                        p-8 text-center shadow-sm
                    "
                >

                    <p class="font-semibold text-gray-700">
                        Nenhuma categoria cadastrada.
                    </p>

                    <p class="mt-2 text-sm text-gray-500">
                        Crie a primeira categoria do seu cardápio.
                    </p>

                </div>

            @endforelse

        </div>

        {{-- Tabela desktop --}}
        <div
            class="
                hidden overflow-hidden rounded-2xl
                bg-white shadow md:block
            "
        >

            <table class="w-full">

                <thead>

                    <tr class="border-b bg-gray-50">

                        <th class="p-4 text-left text-sm font-semibold text-gray-600">
                            Nome
                        </th>

                        <th class="p-4 text-left text-sm font-semibold text-gray-600">
                            Produtos
                        </th>

                        <th class="p-4 text-left text-sm font-semibold text-gray-600">
                            Status
                        </th>

                        <th class="p-4 text-right text-sm font-semibold text-gray-600">
                            Ações
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($categorias as $categoria)

                        <tr class="border-b last:border-b-0">

                            <td class="p-4">

                                <p class="font-semibold text-gray-900">
                                    {{ $categoria->nome }}
                                </p>

                            </td>

                            <td class="p-4 text-gray-600">
                                {{ $categoria->produtos_count }}
                            </td>

                            <td class="p-4">

                                @if($categoria->ativo)

                                    <span
                                        class="
                                            inline-flex rounded-full
                                            bg-green-100 px-3 py-1
                                            text-xs font-semibold text-green-700
                                        "
                                    >
                                        Ativa
                                    </span>

                                @else

                                    <span
                                        class="
                                            inline-flex rounded-full
                                            bg-gray-100 px-3 py-1
                                            text-xs font-semibold text-gray-600
                                        "
                                    >
                                        Inativa
                                    </span>

                                @endif

                            </td>

                            <td class="p-4">

                                <div class="flex justify-end gap-2">

                                    <a
                                        href="{{ route(
                                            'restaurante.categorias.edit',
                                            [
                                                $restauranteAtual->slug,
                                                $categoria
                                            ]
                                        ) }}"
                                        class="
                                            rounded-lg border border-gray-200
                                            px-3 py-2
                                            text-sm font-semibold text-gray-700
                                            transition hover:bg-gray-50
                                        "
                                    >
                                        Editar
                                    </a>

                                    <form
                                        action="{{ route(
                                            'restaurante.categorias.status',
                                            [
                                                $restauranteAtual->slug,
                                                $categoria
                                            ]
                                        ) }}"
                                        method="POST"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            class="
                                                rounded-lg px-3 py-2
                                                text-sm font-semibold transition

                                                @if($categoria->ativo)
                                                    bg-red-50 text-red-700
                                                    hover:bg-red-100
                                                @else
                                                    bg-green-50 text-green-700
                                                    hover:bg-green-100
                                                @endif
                                            "
                                        >
                                            {{ $categoria->ativo
                                                ? 'Desativar'
                                                : 'Ativar' }}
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="4"
                                class="p-8 text-center text-gray-500"
                            >
                                Nenhuma categoria cadastrada neste restaurante.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</x-rimafood.layout>