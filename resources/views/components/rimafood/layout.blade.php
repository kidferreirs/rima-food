<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Rima Food</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body
    class="min-h-screen bg-gray-100 text-gray-900"
    x-data="{ menuAberto: false }"
    x-on:keydown.escape.window="menuAberto = false"
>
    {{-- Fundo escurecido no celular --}}
    <div
        x-cloak
        x-show="menuAberto"
        x-transition.opacity
        x-on:click="menuAberto = false"
        class="fixed inset-0 z-40 bg-black/50 lg:hidden"
    ></div>

    <x-rimafood.sidebar />

    {{-- Cabeçalho mobile --}}
    <header
        class="
            sticky top-0 z-30
            flex h-16 items-center justify-between
            border-b border-gray-200
            bg-white px-4 shadow-sm
            lg:hidden
        "
    >
        <button
            type="button"
            x-on:click="menuAberto = true"
            class="
                inline-flex h-10 w-10
                items-center justify-center
                rounded-xl border border-gray-200
                text-gray-700
                transition hover:bg-gray-100
            "
            aria-label="Abrir menu"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-6 w-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M4 6h16M4 12h16M4 18h16"
                />
            </svg>
        </button>

        <div class="min-w-0 px-3 text-center">
            <p class="truncate text-sm font-bold text-gray-900">
                Rima Food
            </p>

            @isset($restauranteAtual)
                <p class="truncate text-xs text-gray-500">
                    {{ $restauranteAtual->nome }}
                </p>
            @endisset
        </div>

        <div class="h-10 w-10"></div>
    </header>

    <main
        class="
            min-h-screen
            w-full
            overflow-x-hidden
            lg:pl-64
        "
    >
        <div class="w-full">
            {{ $slot }}
        </div>
    </main>
</body>

</html>