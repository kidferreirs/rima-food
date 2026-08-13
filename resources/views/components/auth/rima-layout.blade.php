@props(['titulo','subtitulo'])
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $titulo }} | Rima Food</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-900 antialiased">
<main class="min-h-screen px-4 py-8 sm:px-6 lg:py-12">
    <div class="mx-auto grid min-h-[calc(100vh-6rem)] max-w-6xl overflow-hidden rounded-3xl bg-white shadow-2xl lg:grid-cols-[.95fr_1.05fr]">
        <section class="relative hidden overflow-hidden bg-gradient-to-br from-orange-500 via-orange-600 to-slate-950 p-10 text-white lg:flex lg:flex-col lg:justify-between">
            <div>
                <a href="{{ url('/') }}" class="text-2xl font-black tracking-tight">RIMA <span class="text-orange-200">FOOD</span></a>
                <h1 class="mt-16 max-w-md text-4xl font-black leading-tight tracking-tight">Seu negócio, seus clientes e seu lucro.</h1>
                <p class="mt-5 max-w-md leading-7 text-orange-100">Acesse seu ambiente para organizar seu negócio, pedidos, WhatsApp e toda a operação.</p>
            </div>
            <div class="rounded-2xl border border-white/15 bg-white/10 p-5 backdrop-blur">
                <p class="font-black">Rima Food</p>
                <p class="mt-2 text-sm text-orange-100">Tecnologia simples para pessoas simples.</p>
            </div>
        </section>
        <section class="flex items-center px-5 py-10 sm:px-10 lg:px-14">
            <div class="mx-auto w-full max-w-md">
                <div class="lg:hidden"><a href="{{ url('/') }}" class="text-xl font-black">RIMA <span class="text-orange-500">FOOD</span></a></div>
                <p class="mt-8 text-sm font-black uppercase tracking-[.16em] text-orange-500 lg:mt-0">Acesso ao sistema</p>
                <h2 class="mt-2 text-3xl font-black tracking-tight">{{ $titulo }}</h2>
                <p class="mt-3 leading-7 text-slate-500">{{ $subtitulo }}</p>
                <div class="mt-8">{{ $slot }}</div>
                <p class="mt-8 text-center text-xs text-slate-400">
                    <a href="https://rimatech.cloud" target="_blank" rel="noopener noreferrer" class="hover:text-orange-500">by Rimatech</a>
                </p>
            </div>
        </section>
    </div>
</main>
</body>
</html>
