<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Rimatech Admin</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 text-slate-900">

    <div class="min-h-screen">

        <header class="bg-slate-950 text-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">

                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-orange-400">
                        Rimatech
                    </p>

                    <h1 class="text-xl font-black">
                        Painel Administrativo
                    </h1>
                </div>

                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="font-bold">
                            {{ auth()->user()->name }}
                        </p>

                        <p class="text-xs text-slate-400">
                            Administrador
                        </p>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="rounded-xl bg-red-500 px-4 py-2 text-sm font-bold text-white transition hover:bg-red-600">
                            Sair
                        </button>
                    </form>
                </div>

            </div>
        </header>

        <main class="mx-auto max-w-7xl px-6 py-8">

            <div class="mb-8">
                <h2 class="text-3xl font-black">
                    Visão geral
                </h2>

                <p class="mt-1 text-slate-500">
                    Acompanhe os clientes e assinaturas da Rimatech.
                </p>
            </div>

            {{-- Indicadores --}}
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">

                <a
                    href="{{ route('admin.clientes.index') }}"
                    class="block rounded-2xl bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                >
                    <p class="text-sm font-bold text-slate-500">
                        Clientes
                    </p>

                    <p class="mt-2 text-4xl font-black">
                        {{ $totalClientes }}
                    </p>

                    <p class="mt-3 text-sm font-bold text-orange-500">
                        Ver todos →
                    </p>
                </a>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-bold text-slate-500">
                        Trials ativos
                    </p>

                    <p class="mt-2 text-4xl font-black">
                        {{ $trialsAtivos }}
                    </p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-bold text-slate-500">
                        Assinaturas ativas
                    </p>

                    <p class="mt-2 text-4xl font-black">
                        {{ $assinaturasAtivas }}
                    </p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <p class="text-sm font-bold text-slate-500">
                        Cadastros hoje
                    </p>

                    <p class="mt-2 text-4xl font-black">
                        {{ $cadastrosHoje }}
                    </p>
                </div>

            </div>

            {{-- Clientes recentes --}}
            <section class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm">

                <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-6 py-5">
                    <h3 class="text-lg font-black">
                        Clientes recentes
                    </h3>

                    <a
                        href="{{ route('admin.clientes.index') }}"
                        class="text-sm font-bold text-orange-500 hover:text-orange-600"
                    >
                        Ver todos os clientes →
                    </a>
                </div>

                <div class="overflow-x-auto">

                    <table class="w-full text-left">

                        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Cliente</th>
                                <th class="px-6 py-4">Plano</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Trial até</th>
                                <th class="px-6 py-4">Restaurante</th>
                                <th class="px-6 py-4">Cadastro</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">

                            @forelse($clientesRecentes as $cliente)

                                @php
                                    $assinatura = $cliente->subscription;
                                    $plano = $assinatura?->plan;
                                    $restaurante = $cliente->restaurantes->first();
                                @endphp

                                <tr class="hover:bg-slate-50">

                                    <td class="px-6 py-4">
                                        <a href="{{ route('admin.clientes.show', $cliente) }}"
                                            class="font-bold text-slate-900 hover:text-orange-500 transition">
                                            {{ $cliente->nome }}
                                        </a>

                                        <p class="text-sm text-slate-500">
                                            {{ $cliente->email }}
                                        </p>
                                    </td>

                                    <td class="px-6 py-4 font-semibold">
                                        {{ $plano?->nome ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4">

                                        @if($assinatura?->status === 'trial')
                                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">
                                                Trial
                                            </span>

                                        @elseif($assinatura?->status === 'active')
                                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-700">
                                                Ativo
                                            </span>

                                        @else
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                                                {{ $assinatura?->status ?? 'Sem assinatura' }}
                                            </span>
                                        @endif

                                    </td>

                                    <td class="px-6 py-4">
                                        {{ $assinatura?->trial_ends_at?->format('d/m/Y') ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4">
                                        {{ $restaurante?->nome ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4 text-slate-500">
                                        {{ $cliente->created_at->format('d/m/Y H:i') }}
                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-slate-500">
                                        Nenhum cliente cadastrado.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </section>

        </main>

    </div>

</body>

</html>