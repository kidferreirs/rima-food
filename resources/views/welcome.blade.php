<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Rima Food: cardápio digital, pedidos, WhatsApp com IA e gestão simples para negócios de alimentação.">

    <title> Rima Food — Seu canal próprio de vendas </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    <style>
        ::selection {
            background: #fb923c;
            color: #ffffff;
        }

        .rf-nav {
            display: none;
            align-items: center;
            gap: 1.75rem;
        }

        .rf-product-grid {
            display: grid;
            gap: 1.25rem;
            align-items: start;
        }

        .rf-plans-grid {
            display: grid;
            gap: 1.5rem;
            align-items: stretch;
        }

        .rf-plan-card {
            display: flex;
            min-width: 0;
            height: 100%;
            flex-direction: column;
        }

        .rf-plan-card ul {
            flex: 1;
        }

        @media (min-width: 768px) {
            .rf-product-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1024px) {
            .rf-nav {
                display: flex;
            }

            .rf-plans-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (min-width: 1280px) {
            .rf-product-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
    </style>

</head>

<body class="bg-white text-slate-950 antialiased">

    {{-- HEADER --}}
    <header class="sticky top-0 z-50 border-b border-slate-800 bg-slate-950">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 sm:px-6 lg:px-8">
            <a href="#inicio" class="text-xl font-black tracking-tight text-white">
                RIMA <span class="text-orange-500">FOOD</span>
            </a>

            <nav class="rf-nav text-sm font-bold text-slate-300">
                <a href="#produto" class="hover:text-white">Produto</a>
                <a href="#comparativo" class="hover:text-white">Marketplace x Rima Food</a>
                <a href="#como-funciona" class="hover:text-white">Como funciona</a>
                <a href="#planos" class="hover:text-white">Planos</a>
                <a href="#duvidas" class="hover:text-white">Dúvidas</a>
            </nav>

            <div class="hidden items-center gap-3 lg:flex">
                <a href="{{ route('saas.cadastro') }}"
                    class="rounded-xl bg-orange-500 px-4 py-3 text-sm font-black text-white hover:bg-orange-600">
                    Criar minha loja
                </a>
                <a href="{{ route('login') }}"
                    class="rounded-xl px-4 py-3 text-sm font-black text-slate-300 hover:text-white">
                    Já sou cliente
                </a>
            </div>

            <button id="menu-mobile-btn" type="button"
                class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-700 bg-slate-900 text-white lg:hidden"
                aria-label="Abrir menu" aria-expanded="false" aria-controls="menu-mobile">
                <span id="icone-menu-abrir" class="text-2xl leading-none">☰</span>
                <span id="icone-menu-fechar" class="hidden text-2xl leading-none">✕</span>
            </button>
        </div>

        <div id="menu-mobile" class="hidden border-t border-slate-800 bg-slate-950 lg:hidden">
            <nav class="mx-auto max-w-7xl px-5 py-5 sm:px-6">
                <div class="grid gap-2">
                    <a href="#produto"
                        class="menu-mobile-link rounded-xl px-4 py-3 font-bold text-slate-300 hover:bg-slate-900 hover:text-white">Produto</a>
                    <a href="#comparativo"
                        class="menu-mobile-link rounded-xl px-4 py-3 font-bold text-slate-300 hover:bg-slate-900 hover:text-white">Marketplace
                        x Rima Food</a>
                    <a href="#como-funciona"
                        class="menu-mobile-link rounded-xl px-4 py-3 font-bold text-slate-300 hover:bg-slate-900 hover:text-white">Como
                        funciona</a>
                    <a href="#planos"
                        class="menu-mobile-link rounded-xl px-4 py-3 font-bold text-slate-300 hover:bg-slate-900 hover:text-white">Planos</a>
                    <a href="#duvidas"
                        class="menu-mobile-link rounded-xl px-4 py-3 font-bold text-slate-300 hover:bg-slate-900 hover:text-white">Dúvidas</a>
                </div>

                <div class="mt-4 border-t border-slate-800 pt-4">
                    <a href="{{ route('saas.cadastro') }}"
                        class="mt-2 block rounded-xl bg-orange-500 px-4 py-3 text-center font-black text-white hover:bg-orange-600">
                        Criar minha loja
                    </a>

                    <a href="{{ route('login') }}"
                        class="block rounded-xl px-4 py-3 text-center font-black text-slate-200 hover:bg-slate-900 hover:text-white">
                        Já sou cliente
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <main>

        {{-- HERO --}}
        <section id="inicio" class="bg-slate-950 py-20 text-white sm:py-28">
            <div class="mx-auto grid max-w-7xl grid-cols-1 items-center gap-14 px-5 sm:px-6 lg:grid-cols-2 lg:px-8">
                <div>
                    <div
                        class="inline-flex rounded-full border border-orange-400 bg-orange-500 px-4 py-2 text-xs font-black uppercase tracking-widest text-white">
                        15 dias grátis pelo formulário inteligente
                    </div>

                    <h1 class="mt-7 text-5xl font-black leading-tight tracking-tight sm:text-7xl">
                        Seu negócio
                        <span class="text-orange-500">vende direto.</span></br>
                        O lucro fica com você.
                    </h1>

                    <p class="mt-7 max-w-2xl text-lg leading-8 text-slate-400">
                        Cardápio digital, pedidos, WhatsApp com IA
                        e gestão em uma plataforma simples para o seu negócio
                    </p>

                    <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('saas.cadastro') }}"
                            class="rounded-2xl bg-orange-500 px-7 py-4 text-center font-black text-white hover:bg-orange-600 w-full">
                            Criar minha loja
                        </a>
                        <a href="#produto" class="rounded-2xl border border-slate-700 bg-slate-900 px-7 py-4 text-center font-black text-white
                            hover:bg-slate-800 w-full">Conhecer a plataforma</a>
                    </div>

                    <div class="mt-8 grid gap-x-8 gap-y-3 text-sm font-bold text-slate-400 sm:grid-cols-2">
                        <p>✓ 0% de comissão por pedido</p>
                        <p>✓ Sem cartão para iniciar</p>
                        <p>✓ Seus clientes são seus</p>
                        <p>✓ Cancele quando quiser</p>
                    </div>
                </div>

                {{-- MOCKUP --}}
                <div class="rounded-3xl border border-slate-700 bg-slate-900 p-4 shadow-2xl">
                    <div class="overflow-hidden rounded-2xl bg-slate-100 text-slate-950">
                        <div class="flex items-center justify-between border-b border-slate-200 bg-white px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-orange-100 text-xl">
                                    🍔
                                </div>

                                <div>
                                    <p class="font-black">Painel Rima Food</p>
                                    <p class="text-xs text-slate-500">Loja ativa</p>
                                </div>
                            </div>

                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-black text-green-700">
                                Online
                            </span>
                        </div>

                        <div class="grid gap-4 p-5 sm:grid-cols-3">

                            @foreach([
                                    ['Pedidos hoje', '48'],
                                    ['Faturamento', 'R$ 2.384'],
                                    ['Novos clientes', '19'],
                                ] as [$titulo, $valor])
                                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                    <p class="text-xs text-slate-500">{{ $titulo }}</p>
                                    <p class="mt-3 text-xl font-black">{{ $valor }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="grid gap-4 px-5 pb-5 sm:grid-cols-2">
                            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                                <div class="flex items-center justify-between">
                                    <p class="font-black">Vendas da semana</p>
                                    <span class="text-xs font-black text-green-600">
                                        +31%
                                    </span>
                                </div>

                                <div class="mt-8 flex h-40 items-end gap-3">
                                    <div class="h-16 flex-1 rounded-t-lg bg-orange-300"></div>
                                    <div class="h-24 flex-1 rounded-t-lg bg-orange-400"></div>
                                    <div class="h-20 flex-1 rounded-t-lg bg-orange-300"></div>
                                    <div class="h-32 flex-1 rounded-t-lg bg-orange-500"></div>
                                    <div class="h-28 flex-1 rounded-t-lg bg-orange-400"></div>
                                    <div class="h-36 flex-1 rounded-t-lg bg-orange-500"></div>
                                    <div class="h-40 flex-1 rounded-t-lg bg-orange-600"></div>
                                </div>
                            </div>

                            <div class="rounded-2xl bg-slate-950 p-5 text-white">
                                <p class="text-xs font-black uppercase tracking-widest text-orange-400">
                                    IA ativa
                                </p>

                                <p class="mt-4 text-2xl font-black">Atendendo clientes agora.</p>

                                <div class="mt-6 rounded-xl bg-slate-800 p-4 text-sm text-slate-300">
                                    “Quero uma pizza grande de calabresa.”
                                </div>

                                <div class="mt-3 rounded-xl bg-orange-500 p-4 text-sm font-bold">
                                    “Deseja adicionar borda recheada?”
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- FAIXA --}}
        <section class="border-y border-slate-200 bg-white py-5">
            <div class="mx-auto max-w-7xl px-5">

                <div class="grid grid-cols-1 gap-3 text-center text-sm font-black uppercase tracking-wider text-slate-500 sm:flex sm:flex-wrap sm:items-center
                    sm:justify-center sm:gap-x-8 sm:gap-y-3">

                    <div class="flex items-center justify-center gap-3">
                        <span class="text-orange-500">✦</span>
                        <span>0% de comissão</span>
                    </div>

                    <div class="flex items-center justify-center gap-3">
                        <span class="text-orange-500">✦</span>
                        <span>Cardápio digital</span>
                    </div>

                    <div class="flex items-center justify-center gap-3">
                        <span class="text-orange-500">✦</span>
                        <span>WhatsApp com IA</span>
                    </div>

                    <div class="flex items-center justify-center gap-3">
                        <span class="text-orange-500">✦</span>
                        <span>Pedidos organizados</span>
                    </div>

                    <div class="flex items-center justify-center gap-3">
                        <span class="text-orange-500">✦</span>
                        <span>No ar em minutos</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- PRODUTO --}}
        <section id="produto" class="bg-slate-50 py-20 sm:py-28">
            <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
                <div class="grid gap-8">
                    <div>
                        <p class="text-sm font-black uppercase tracking-widest text-orange-500">
                            Tudo em um só lugar
                        </p>

                        <h2 class="mt-5 text-4xl font-black leading-tight tracking-tight sm:text-6xl">
                            Simples para usar. Poderoso para vender.
                        </h2>
                    </div>

                    <p>O Rima Food reúne o que seu negócio precisa sem transformar o sistema em uma ferramenta
                        complicada.</p>
                </div>

                <div class="rf-product-grid mt-14 items-start">

                    {{-- Card principal --}}
                    <article class="rounded-3xl border border-orange-200 bg-orange-50 p-5 sm:p-8 md:col-span-2">

                        {{-- Cabeçalho --}}
                        <div class="flex items-start gap-4 sm:items-center sm:gap-5">

                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-orange-500 text-2xl text-white shadow-lg sm:h-16 sm:w-16 sm:text-3xl">
                                📱
                            </div>

                            <div class="min-w-0 flex-1">

                                <h3 class="text-2xl font-black leading-tight sm:text-4xl">
                                    Cardápio digital
                                </h3>

                                <p class="mt-2 text-base leading-6 text-slate-600 sm:text-lg sm:leading-7">
                                    Seu menu com sua marca, seu link e nenhuma concorrência na tela.
                                </p>
                            </div>
                        </div>

                        {{-- Cards internos --}}
                        <div class="mt-10 grid gap-6 sm:grid-cols-2">

                            {{-- Card escuro --}}
                            <div class="rounded-3xl bg-slate-950 p-8 text-white shadow-xl">

                                <span
                                    class="inline-flex rounded-full bg-orange-500/20 px-3 py-1 text-2x1 font-black uppercase tracking-widest text-orange-400">

                                    🚀 Canal próprio

                                </span>

                                <h4 class="mt-6 text-4xl font-black leading-tight">
                                    Sua marca.<br>
                                    Seu link.<br>
                                    Seus clientes.
                                </h4>

                            </div>

                            {{-- Card claro --}}
                            <div class="rounded-3xl border border-orange-200 bg-white p-8 shadow-lg">

                                <h4 class="text-2xl font-black">
                                    Divulgue onde quiser
                                </h4>

                                <div class="mt-8 space-y-5">

                                    <div class="flex items-center gap-4">
                                        <img src="{{ asset('images/rima-food/icones/icons-whatsapp.png') }}"
                                            alt="WhatsApp" class="h-7 w-7 object-contain">

                                        <span class="font-medium text-slate-700">
                                            WhatsApp
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <img src="{{ asset('images/rima-food/icones/icons-instagram.png') }}"
                                            alt="Instagram" class="h-7 w-7 object-contain">

                                        <span class="font-medium text-slate-700">
                                            Instagram
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <img src="{{ asset('images/rima-food/icones/icons-google-maps.png') }}"
                                            alt="Google" class="h-7 w-7 object-contain">

                                        <span class="font-medium text-slate-700">
                                            Google Maps
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <img src="{{ asset('images/rima-food/icones/icons-qr-code.png') }}"
                                            alt="QR Code" class="h-7 w-7 object-contain">

                                        <span class="font-medium text-slate-700">
                                            QR Code
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>

                    {{-- Celular --}}
                    <div class="relative flex justify-center self-start lg:-ml-10">

                        {{-- Glow --}}
                        <div class="absolute top-24 h-72 w-72 rounded-full bg-orange-400/20 blur-3xl">
                        </div>

                        <img src="{{ asset('images/rima-food/menu-celular.png') }}" alt="Cardápio digital do Rima Food"
                            class="relative z-10 w-full max-w-sm object-contain">
                    </div>
                </div>
            </div>
            </div>
        </section>

        {{-- COMPARATIVO --}}
        <section id="comparativo" class="bg-slate-950 py-20 text-white sm:py-28">
            <div class="mx-auto max-w-7xl px-5 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-4xl text-center">
                    <p class="text-sm font-black uppercase tracking-widest text-orange-400">
                        Todo pedido tem um destino
                    </p>

                    <h2 class="mt-6 text-5xl font-black leading-tight tracking-tight sm:text-7xl">
                        Você vende.
                        <span class="block text-slate-500">Quem está ficando mais forte?</span>
                    </h2>

                    <p class="mx-auto mt-6 max-w-2xl text-lg leading-8 text-slate-400">
                        Veja o caminho de um pedido nos dois cenários diferentes.
                    </p>
                </div>

                <div class="mt-14 grid gap-7 lg:grid-cols-2">
                    <article class="rounded-3xl border border-red-500 bg-red-950 p-7">
                        <p class="text-xs font-black uppercase tracking-widest text-red-300">
                            Marketplace
                        </p>

                        <h3 class="mt-3 text-4xl font-black">App de delivery</h3>

                        <div class="mt-8 space-y-3">
                            @foreach([
                                    'Cliente faz o pedido',
                                    'Parte do valor vira comissão',
                                    'Cliente continua no aplicativo',
                                    'Concorrentes aparecem na tela',
                                    'Você depende da plataforma novamente',
                                ] as $passo)
                                <div class="rounded-2xl border border-red-800 bg-red-900 p-4 font-bold text-red-100">
                                    ✕ {{ $passo }}
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-7 rounded-2xl bg-red-600 p-6">
                            <p class="text-xs font-black uppercase tracking-widest">Resultado</p>

                            <p class="mt-2 text-3xl font-black">Você trabalha.</p>

                            <p class="text-red-100">O marketplace cresce.</p>
                        </div>
                    </article>

                    <article class="rounded-3xl border border-orange-500 bg-slate-900 p-7">
                        <p class="text-xs font-black uppercase tracking-widest text-orange-400">
                            Rima Food
                        </p>

                        <h3 class="mt-3 text-4xl font-black">App Rima Food</h3>

                        <div class="mt-8 space-y-3">
                            @foreach([
                                    'Cliente faz o pedido',
                                    'O valor fica no seu negócio',
                                    'Cliente entra no seu WhatsApp',
                                    'Sua marca fica na memória',
                                    'Cada venda fortalece o seu negócio',
                                ] as $passo)
                                <div class="rounded-2xl border border-orange-700 bg-slate-800 p-4 font-bold text-slate-100">
                                    ✓ {{ $passo }}
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-7 rounded-2xl bg-orange-500 p-6">
                            <p class="text-xs font-black uppercase tracking-widest">Resultado</p>

                            <p class="mt-2 text-3xl font-black">Você vende.</p>

                            <p class="text-orange-100">O seu negócio prospera.</p>
                        </div>
                    </article>
                </div>

                <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach([
                            ['0%', 'de comissão por pedido'],
                            ['100%', 'dos clientes no seu canal'],
                            ['Sua marca', 'sua logo, seu nome'],
                            ['WhatsApp', 'para vender e fidelizar'],
                        ] as [$valor, $texto])
                        <div class="rounded-3xl border border-slate-800 bg-slate-900 p-7 text-center">
                            <p class="text-3xl font-black text-orange-400">{{ $valor }}</p>

                            <p class="mt-2 text-sm text-slate-400">{{ $texto }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- IA --}}
        <section class="bg-white py-20 sm:py-28">
            <div class="
            mx-auto grid max-w-7xl
            grid-cols-1 items-center
            gap-14 px-5 sm:px-6
            lg:grid-cols-2 lg:px-8
        ">
                <div>
                    <p class="
                    text-sm font-black
                    uppercase tracking-widest
                    text-orange-500
                ">
                        Atendimento inteligente
                    </p>

                    <h2 class="
                    mt-5 text-4xl font-black
                    leading-tight tracking-tight
                    sm:text-6xl
                ">
                        A IA atende.
                        Você cuida do negócio.
                    </h2>

                    <p class="
                    mt-6 max-w-xl
                    text-lg leading-8
                    text-slate-600
                ">
                        A IA entende o pedido, pergunta opções,
                        sugere adicionais, monta o carrinho
                        e conduz o cliente até a confirmação.
                    </p>

                    <div class="mt-8 grid gap-4">
                        @foreach([
                                'Entende linguagem natural',
                                'Pergunta opções obrigatórias',
                                'Sugere adicionais e complementos',
                                'Mantém o contexto da conversa',
                            ] as $item)
                            <div class="
                                                                                                            flex items-center gap-4
                                                                                                            rounded-2xl
                                                                                                            border border-slate-200
                                                                                                            bg-slate-50 p-4
                                                                                                        ">
                                <span class="
                                                                                                                flex h-9 w-9
                                                                                                                items-center justify-center
                                                                                                                rounded-xl bg-orange-100
                                                                                                                font-black text-orange-600
                                                                                                            ">
                                    ✓
                                </span>

                                <span class="font-bold">
                                    {{ $item }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <div class="flex items-center gap-3 pb-5 text-white">

                        <img src="{{ asset('images/rima-food/whatsapp-ia-celular.png') }}"
                            alt="Cardápio digital do Rima Food exibido em um celular"
                            class="mx-auto h-auto w-full max-w-sm object-contain">

                    </div>
                </div>
            </div>
        </section>

        {{-- COMO FUNCIONA --}}
        <section id="como-funciona" class="bg-slate-50 py-20 sm:py-28">
            <div class="
            mx-auto max-w-7xl
            px-5 sm:px-6 lg:px-8
        ">
                <div class="mx-auto max-w-4xl text-center">
                    <p class="
                    text-sm font-black
                    uppercase tracking-widest
                    text-orange-500
                ">
                        Da ideia ao primeiro pedido
                    </p>

                    <h2 class="
                    mt-5 text-4xl font-black
                    leading-tight tracking-tight
                    sm:text-6xl
                ">
                        Sua loja no ar sem complicação.
                    </h2>

                    <p class="
                    mx-auto mt-6 max-w-2xl
                    text-lg leading-8
                    text-slate-600
                ">
                        O formulário inteligente prepara a base.
                        Você ajusta o que quiser e começa a vender.
                    </p>
                </div>

                <div class="
                mt-14 grid gap-5
                md:grid-cols-2
                lg:grid-cols-4
            ">
                    @foreach([
                            [
                                '01',
                                'Conte sobre o negócio',
                                'Informe nome, segmento e escolha o plano.',
                            ],
                            [
                                '02',
                                'Ambiente criado',
                                'O sistema aplica banner, cores e estrutura inicial.',
                            ],
                            [
                                '03',
                                'Importe o cardápio',
                                'A IA organiza produtos, categorias e preços.',
                            ],
                            [
                                '04',
                                'Compartilhe e venda',
                                'Use seu link, WhatsApp, Instagram e QR Code.',
                            ],
                        ] as [$numero, $titulo, $texto])
                        <article class="
                                                                                                        rounded-3xl
                                                                                                        border border-slate-200
                                                                                                        bg-white p-7
                                                                                                    ">
                            <span class="
                                                                                                            flex h-14 w-14
                                                                                                            items-center justify-center
                                                                                                            rounded-2xl bg-orange-500
                                                                                                            font-black text-white
                                                                                                        ">
                                {{ $numero }}
                            </span>

                            <h3 class="
                                                                                                            mt-7 text-xl
                                                                                                            font-black
                                                                                                        ">
                                {{ $titulo }}
                            </h3>

                            <p class="
                                                                                                            mt-3 leading-7
                                                                                                            text-slate-600
                                                                                                        ">
                                {{ $texto }}
                            </p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- PLANOS --}}
        <section id="planos" class="bg-white py-20 sm:py-28">
            <div class="
            mx-auto max-w-7xl
            px-5 sm:px-6 lg:px-8
        ">
                <div class="mx-auto max-w-4xl text-center">
                    <p class="
                    text-sm font-black
                    uppercase tracking-widest
                    text-orange-500
                ">
                        Planos simples
                    </p>

                    <h2 class="
                    mt-5 text-4xl font-black
                    leading-tight tracking-tight
                    sm:text-6xl
                ">
                        Comece com o que precisa.
                        Evolua quando quiser.
                    </h2>
                </div>

                <div class="rf-plans-grid mt-14">
                    <article class="
                    rf-plan-card
                    rounded-3xl
                    border border-slate-200
                    bg-white p-8
                    shadow-sm
                ">
                        <p class="
                        text-sm font-black
                        uppercase tracking-widest
                        text-orange-500
                    ">
                            Rima Menu
                        </p>

                        <p class="
                        mt-4 min-h-14
                        text-lg text-slate-600
                    ">
                            Para criar seu canal próprio.
                        </p>

                        <p class="
                        mt-7 text-5xl
                        font-black
                    ">
                            R$ 39,90
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            por mês
                        </p>

                        <ul class="
                        mt-8 space-y-4
                        text-sm font-bold
                        text-slate-700
                    ">
                            <li>✓ Cardápio digital</li>
                            <li>✓ Produtos e categorias</li>
                            <li>✓ Import Menu</li>
                            <li>✓ Clientes</li>
                            <li>✓ Dashboard de pedidos</li>
                        </ul>

                        <a href="{{ route('saas.cadastro') }}" class="
                        mt-9 flex w-full
                        justify-center
                        rounded-2xl bg-slate-950
                        px-5 py-4
                        font-black text-white
                        hover:bg-slate-800
                    ">
                            Criar minha loja
                        </a>
                    </article>

                    <article class="
                    rf-plan-card
                    rounded-3xl
                    bg-slate-950 p-8
                    text-white shadow-2xl
                ">
                        <div class="
                        flex items-center
                        justify-between gap-4
                    ">
                            <p class="
                            text-sm font-black
                            uppercase tracking-widest
                            text-orange-400
                        ">
                                Rima Menu + IA
                            </p>

                            <span class="
                            rounded-full
                            bg-orange-500
                            px-3 py-1
                            text-xs font-black
                        ">
                                Mais escolhido
                            </span>
                        </div>

                        <p class="
                        mt-4 min-h-14
                        text-lg text-slate-400
                    ">
                            Para vender pelo WhatsApp.
                        </p>

                        <p class="
                        mt-7 text-5xl
                        font-black
                    ">
                            R$ 79,90
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            por mês
                        </p>

                        <ul class="
                        mt-8 space-y-4
                        text-sm font-bold
                        text-slate-300
                    ">
                            <li>✓ Tudo do Rima Menu</li>
                            <li>✓ Pedidos</li>
                            <li>✓ WhatsApp com IA</li>
                            <li>✓ Carrinho inteligente</li>
                            <li>✓ Opções e adicionais</li>
                        </ul>

                        <a href="{{ route('saas.cadastro') }}" class="
                        mt-9 flex w-full
                        justify-center
                        rounded-2xl bg-orange-500
                        px-5 py-4
                        font-black text-white
                        hover:bg-orange-600
                    ">
                            Criar minha loja
                        </a>
                    </article>

                    <article class="
                    rounded-3xl
                    border border-slate-200
                    bg-white p-8
                    shadow-sm
                ">
                        <p class="
                        text-sm font-black
                        uppercase tracking-widest
                        text-orange-500
                    ">
                            Rima Food
                        </p>

                        <p class="
                        mt-4 min-h-14
                        text-lg text-slate-600
                    ">
                            Para controlar toda a operação.
                        </p>

                        <p class="
                        mt-7 text-5xl
                        font-black
                    ">
                            R$ 149,90
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            por mês
                        </p>

                        <ul class="
                        mt-8 space-y-4
                        text-sm font-bold
                        text-slate-700
                    ">
                            <li>✓ Tudo do Rima Menu + IA</li>
                            <li>✓ Cozinha</li>
                            <li>✓ Relatórios</li>
                            <li>✓ Delivery e consumo local</li>
                            <li>✓ Operação completa</li>
                        </ul>

                        <a href="{{ route('saas.cadastro') }}" class="
                        mt-9 flex w-full
                        justify-center
                        rounded-2xl bg-slate-950
                        px-5 py-4
                        font-black text-white
                        hover:bg-slate-800
                    ">
                            Criar minha loja
                        </a>
                    </article>
                </div>

                <p class="
                mt-7 text-center
                text-sm font-bold
                text-slate-500
            ">
                    Todos os planos começam com 15 dias grátis.
                </p>
            </div>
        </section>

        {{-- FAQ --}}
        <section id="duvidas" class="bg-slate-50 py-20 sm:py-28">
            <div class="
            mx-auto grid max-w-7xl
            grid-cols-1 gap-12
            px-5 sm:px-6
            lg:grid-cols-2 lg:px-8
        ">
                <div>
                    <p class="
                    text-sm font-black
                    uppercase tracking-widest
                    text-orange-500
                ">
                        Dúvidas
                    </p>

                    <h2 class="
                    mt-5 text-4xl font-black
                    leading-tight tracking-tight
                    sm:text-6xl
                ">
                        Tudo o que você precisa saber.
                    </h2>

                    <p class="
                    mt-6 max-w-md
                    text-lg leading-8
                    text-slate-600
                ">
                        Sem letras pequenas, sem taxas escondidas
                        e sem complicação.
                    </p>
                </div>

                <div class="space-y-3">
                    @foreach([
                            [
                                'Quanto tempo leva para colocar minha loja no ar?',
                                'Poucos minutos. Você preenche o formulário inteligente e o ambiente é criado automaticamente.',
                            ],
                            [
                                'O Rima Food cobra comissão por pedido?',
                                'Não. O Rima Food trabalha com valor fixo mensal.',
                            ],
                            [
                                'Meu cliente precisa instalar aplicativo?',
                                'Não. O cardápio abre diretamente no navegador.',
                            ],
                            [
                                'Já uso marketplace. Posso usar os dois?',
                                'Sim. Você pode manter o marketplace enquanto fortalece seu canal próprio.',
                            ],
                            [
                                'Preciso ter CNPJ?',
                                'O cadastro aceita CPF ou CNPJ.',
                            ],
                            [
                                'Como funciona o WhatsApp com IA?',
                                'A IA entende o pedido, pergunta opções e conduz a conversa até a confirmação.',
                            ],
                        ] as [$pergunta, $resposta])
                        <details class="
                                                                                                        rounded-2xl
                                                                                                        border border-slate-200
                                                                                                        bg-white p-5
                                                                                                    ">
                            <summary class="
                                                                                                            cursor-pointer
                                                                                                            font-black
                                                                                                        ">
                                {{ $pergunta }}
                            </summary>

                            <p class="
                                                                                                            mt-4 leading-7
                                                                                                            text-slate-600
                                                                                                        ">
                                {{ $resposta }}
                            </p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- CTA --}}
        <section class="
        bg-orange-500 py-20
        text-white sm:py-24
    ">
            <div class="
            mx-auto max-w-5xl
            px-5 text-center sm:px-6
        ">
                <p class="
                text-sm font-black
                uppercase tracking-widest
                text-orange-100
            ">
                    Seu canal. Sua marca. Seu lucro.
                </p>

                <h2 class="
                mt-6 text-5xl font-black
                leading-tight tracking-tight
                sm:text-7xl
            ">
                    O próximo pedido pode ser 100% seu.
                </h2>

                <p class="
                mx-auto mt-7 max-w-2xl
                text-lg leading-8
                text-orange-100
            ">
                    Crie sua loja pelo formulário inteligente
                    e comece seus 15 dias de teste.
                </p>

                <div class="
                mt-9 flex flex-col
                justify-center gap-3
                sm:flex-row
            ">
                    <a href="{{ route('saas.cadastro') }}" class="
                    rounded-2xl bg-white
                    px-8 py-4
                    text-lg font-black
                    text-orange-600
                    hover:bg-orange-50
                ">
                        Criar minha loja
                    </a>

                    <!-- <a href="{{ route('login') }}" class="
                    rounded-2xl
                    border border-orange-200
                    bg-orange-600
                    px-8 py-4
                    text-lg font-black
                    text-white
                    hover:bg-orange-700
                ">
                        Já sou cliente
                    </a> -->
                </div>

                <p class="
                mt-5 text-sm
                font-bold text-orange-100
            ">
                    Sem cartão · Sem comissão por pedido
                </p>
            </div>
        </section>

    </main>

    <footer class="
        bg-slate-950 py-10
        text-slate-500
    ">
        <div class="
            mx-auto flex max-w-7xl
            flex-col gap-6 px-5
            sm:px-6 md:flex-row
            md:items-center
            md:justify-between lg:px-8
        ">
            <div>
                <p class="font-black text-white">
                    RIMA
                    <span class="text-orange-500">
                        FOOD
                    </span>
                </p>

                <p class="mt-2 text-sm">
                    Seu negócio. Sua marca. Seu lucro.
                </p>
            </div>

            <div class="
                flex flex-wrap gap-5
                text-sm font-bold
            ">
                <a href="#produto" class="hover:text-white">
                    Produto
                </a>

                <a href="#planos" class="hover:text-white">
                    Planos
                </a>

                <a href="#duvidas" class="hover:text-white">
                    Dúvidas
                </a>

                <a href="https://rimatech.cloud" target="_blank" rel="noopener noreferrer"
                    class="hover:text-orange-400">
                    by Rimatech
                </a>
            </div>
        </div>
    </footer>

    <script>
        const menuMobileBtn = document.getElementById('menu-mobile-btn');
        const menuMobile = document.getElementById('menu-mobile');
        const iconeAbrir = document.getElementById('icone-menu-abrir');
        const iconeFechar = document.getElementById('icone-menu-fechar');

        function definirMenuMobile(aberto) {
            menuMobile.classList.toggle('hidden', !aberto);
            iconeAbrir.classList.toggle('hidden', aberto);
            iconeFechar.classList.toggle('hidden', !aberto);
            menuMobileBtn.setAttribute('aria-expanded', aberto ? 'true' : 'false');
        }

        menuMobileBtn.addEventListener('click', () => {
            definirMenuMobile(menuMobileBtn.getAttribute('aria-expanded') !== 'true');
        });

        document.querySelectorAll('.menu-mobile-link').forEach(link => {
            link.addEventListener('click', () => definirMenuMobile(false));
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) definirMenuMobile(false);
        });
    </script>

</body>

</html>