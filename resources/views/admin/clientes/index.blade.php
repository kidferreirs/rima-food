<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes | Rimatech Admin</title>
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
                    <a href="{{ route('admin.dashboard') }}"
                        class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-bold hover:bg-slate-700">
                        ← Dashboard
                    </a>

                    <a href="{{ route('admin.clientes.create') }}"
                        class="rounded-xl bg-orange-500 px-5 py-3 font-bold text-white hover:bg-orange-600">
                        + Novo cliente
                    </a>

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
                <p class="text-sm font-bold uppercase tracking-[0.15em] text-orange-500">
                    Clientes
                </p>

                <div class="mt-1 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h2 class="text-3xl font-black">
                            Todos os clientes
                        </h2>

                        <p class="mt-1 text-slate-500">
                            Busque, filtre e acompanhe todas as contas cadastradas na Rimatech.
                        </p>
                    </div>

                    <div class="text-sm font-bold text-slate-500">
                        {{ $clientes->total() }} cliente(s)
                    </div>
                </div>
            </div>

            <section class="rounded-2xl bg-white p-6 shadow-sm">

                <form method="GET" action="{{ route('admin.clientes.index') }}" class="grid gap-4 lg:grid-cols-5">

                    <div class="lg:col-span-2">
                        <label class="text-sm font-bold text-slate-600">
                            Buscar
                        </label>

                        <input type="text" name="busca" value="{{ $busca }}"
                            placeholder="Cliente, e-mail, telefone ou restaurante"
                            class="mt-1 w-full rounded-xl border border-slate-300 p-3">
                    </div>

                    <div>
                        <label class="text-sm font-bold text-slate-600">
                            Status
                        </label>

                        <select name="status" class="mt-1 w-full rounded-xl border border-slate-300 p-3">
                            <option value="">Todos</option>
                            <option value="trial" @selected($status === 'trial')>Trial</option>
                            <option value="active" @selected($status === 'active')>Ativo</option>
                            <option value="expired" @selected($status === 'expired')>Trial expirado</option>
                            <option value="none" @selected($status === 'none')>Sem assinatura</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-bold text-slate-600">
                            Plano
                        </label>

                        <select name="plan_id" class="mt-1 w-full rounded-xl border border-slate-300 p-3">
                            <option value="">Todos</option>

                            @foreach($plans as $itemPlan)
                                <option value="{{ $itemPlan->id }}" @selected((string) $planId === (string) $itemPlan->id)>
                                    {{ $itemPlan->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-bold text-slate-600">
                            Conta
                        </label>

                        <select name="conta" class="mt-1 w-full rounded-xl border border-slate-300 p-3">
                            <option value="">Todas</option>
                            <option value="ativa" @selected($conta === 'ativa')>Ativas</option>
                            <option value="suspensa" @selected($conta === 'suspensa')>Suspensas</option>
                        </select>
                    </div>

                    <div class="flex flex-wrap gap-3 lg:col-span-5">
                        <button class="rounded-xl bg-slate-950 px-5 py-3 font-bold text-white hover:bg-slate-800">
                            Filtrar
                        </button>

                        <a href="{{ route('admin.clientes.index') }}"
                            class="rounded-xl border border-slate-300 px-5 py-3 font-bold text-slate-700 hover:bg-slate-50">
                            Limpar filtros
                        </a>
                    </div>

                </form>

            </section>

            <section class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm">

                <div class="overflow-x-auto">

                    <table class="w-full text-left">

                        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Cliente</th>
                                <th class="px-6 py-4">Restaurante</th>
                                <th class="px-6 py-4">Plano</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Trial</th>
                                <th class="px-6 py-4">WhatsApp</th>
                                <th class="px-6 py-4">Cadastro</th>
                                <th class="px-6 py-4 text-right">Ação</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">

                            @forelse($clientes as $cliente)

                                @php
                                    $assinatura = $cliente->subscription;
                                    $plano = $assinatura?->plan;
                                    $restaurante = $cliente->restaurantes->first();

                                    $trialExpirado =
                                        $assinatura?->status === 'trial'
                                        && $assinatura?->trial_ends_at
                                        && $assinatura->trial_ends_at->isPast();
                                @endphp

                                <tr class="hover:bg-slate-50">

                                    <td class="px-6 py-4">
                                        <p class="font-bold">
                                            {{ $cliente->nome }}
                                        </p>

                                        <p class="text-sm text-slate-500">
                                            {{ $cliente->email ?? '—' }}
                                        </p>

                                        @if(!$cliente->ativo)
                                            <span
                                                class="mt-2 inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700">
                                                Conta suspensa
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4">
                                        {{ $restaurante?->nome ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4 font-semibold">
                                        {{ $plano?->nome ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4">

                                        @if($assinatura?->status === 'active')
                                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-700">
                                                Ativo
                                            </span>

                                        @elseif($trialExpirado)
                                            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-700">
                                                Expirado
                                            </span>

                                        @elseif($assinatura?->status === 'trial')
                                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">
                                                Trial
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

                                        @if($restaurante?->evolution_status === 'open')
                                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-700">
                                                Conectado
                                            </span>
                                        @else
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                                                Não conectado
                                            </span>
                                        @endif

                                    </td>

                                    <td class="px-6 py-4 text-slate-500">
                                        {{ $cliente->created_at->format('d/m/Y H:i') }}
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('admin.clientes.show', $cliente) }}"
                                            class="inline-flex rounded-lg bg-orange-500 px-4 py-2 text-sm font-bold text-white hover:bg-orange-600">
                                            Ver cliente
                                        </a>
                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                                        Nenhum cliente encontrado com esses filtros.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

                @if($clientes->hasPages())
                    <div class="border-t border-slate-100 px-6 py-5">
                        {{ $clientes->links() }}
                    </div>
                @endif

            </section>

        </main>

    </div>

</body>

</html>