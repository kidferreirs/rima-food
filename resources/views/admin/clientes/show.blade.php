<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $account->nome }} | Rimatech Admin</title>

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

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.dashboard') }}"
                        class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-bold hover:bg-slate-700">
                        ← Dashboard
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
                    Cliente
                </p>

                <h2 class="mt-1 text-3xl font-black">
                    {{ $account->nome }}
                </h2>

                <p class="mt-1 text-slate-500">
                    Cadastro em {{ $account->created_at->format('d/m/Y H:i') }}
                </p>

            </div>

            <div class="grid gap-6 lg:grid-cols-2">

                {{-- Conta --}}
                <section class="rounded-2xl bg-white p-6 shadow-sm">

                    <h3 class="text-lg font-black">
                        👤 Conta
                    </h3>

                    <div class="mt-5 space-y-4 text-sm">

                        <div>
                            <p class="text-slate-400">Nome</p>
                            <p class="font-bold">{{ $account->nome }}</p>
                        </div>

                        <div>
                            <p class="text-slate-400">E-mail</p>
                            <p class="font-bold">{{ $account->email ?? '—' }}</p>
                        </div>

                        <div>
                            <p class="text-slate-400">Telefone</p>
                            <p class="font-bold">{{ $account->telefone ?? '—' }}</p>
                        </div>

                        <div>
                            <p class="text-slate-400">Documento</p>
                            <p class="font-bold">{{ $account->documento ?? '—' }}</p>
                        </div>

                        <div>
                            <p class="text-slate-400">Status</p>

                            @if($account->ativo)
                                <span class="inline-flex rounded-full bg-green-100 px-3 py-1 font-bold text-green-700">
                                    Ativo
                                </span>
                            @else
                                <span class="inline-flex rounded-full bg-red-100 px-3 py-1 font-bold text-red-700">
                                    Inativo
                                </span>
                            @endif
                        </div>

                    </div>

                </section>

                {{-- Assinatura --}}
                <section class="rounded-2xl bg-white p-6 shadow-sm">

                    <h3 class="text-lg font-black">
                        💳 Assinatura
                    </h3>

                    <div class="mt-5 space-y-4 text-sm">

                        <div>
                            <p class="text-slate-400">Plano atual</p>
                            <p class="font-bold">
                                {{ $plan?->nome ?? 'Sem plano' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-slate-400">Valor</p>
                            <p class="font-bold">
                                @if($plan)
                                    R$ {{ number_format($plan->valor, 2, ',', '.') }}
                                @else
                                    —
                                @endif
                            </p>
                        </div>

                        <div>
                            <p class="text-slate-400">Status</p>
                            <p class="font-bold">
                                {{ ucfirst($subscription?->status ?? 'Sem assinatura') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-slate-400">Trial até</p>
                            <p class="font-bold">
                                {{ $subscription?->trial_ends_at?->format('d/m/Y') ?? '—' }}
                            </p>
                        </div>

                        @if($diasRestantesTrial !== null)
                            <div>
                                <p class="text-slate-400">Dias restantes</p>

                                <p class="text-2xl font-black text-orange-500">
                                    {{ $diasRestantesTrial }}
                                </p>
                            </div>
                        @endif

                    </div>

                </section>

                {{-- Restaurante --}}
                <section class="rounded-2xl bg-white p-6 shadow-sm">

                    <h3 class="text-lg font-black">
                        🍔 Restaurante
                    </h3>

                    <div class="mt-5 space-y-4 text-sm">

                        <div>
                            <p class="text-slate-400">Nome</p>
                            <p class="font-bold">
                                {{ $restaurante?->nome ?? '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-slate-400">Segmento</p>
                            <p class="font-bold">
                                {{ ucfirst($restaurante?->segmento ?? '—') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-slate-400">Cidade</p>
                            <p class="font-bold">
                                {{ $restaurante?->cidade ?? '—' }}
                                @if($restaurante?->estado)
                                    / {{ $restaurante->estado }}
                                @endif
                            </p>
                        </div>

                        <div>
                            <p class="text-slate-400">WhatsApp</p>

                            @if($restaurante?->evolution_status === 'open')
                                <span class="inline-flex rounded-full bg-green-100 px-3 py-1 font-bold text-green-700">
                                    Conectado
                                </span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 font-bold text-slate-600">
                                    Não conectado
                                </span>
                            @endif
                        </div>

                    </div>

                </section>

                {{-- Usuário --}}
                <section class="rounded-2xl bg-white p-6 shadow-sm">

                    <h3 class="text-lg font-black">
                        🔐 Usuário principal
                    </h3>

                    <div class="mt-5 space-y-4 text-sm">

                        <div>
                            <p class="text-slate-400">Nome</p>
                            <p class="font-bold">
                                {{ $user?->name ?? '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-slate-400">E-mail</p>
                            <p class="font-bold">
                                {{ $user?->email ?? '—' }}
                            </p>
                        </div>

                    </div>

                </section>
            </div>

            {{-- GERENCIAMENTO DO CLIENTE --}}
            <section class="mt-6 rounded-2xl bg-white p-6 shadow-sm">

                <h3 class="text-lg font-black">
                    ⚙️ Gerenciar cliente
                </h3>

                @if(session('success'))
                    <div class="mt-4 rounded-xl bg-green-100 px-4 py-3 font-bold text-green-700">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mt-4 rounded-xl bg-red-100 px-4 py-3 font-bold text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-4">

                    {{-- Estender trial --}}
                    <form action="{{ route('admin.clientes.trial.estender', $account) }}" method="POST"
                        class="rounded-xl border border-slate-200 p-4">
                        @csrf

                        <p class="font-bold">Estender trial</p>

                        <input type="number" name="dias" min="1" max="365" value="7"
                            class="mt-3 w-full rounded-lg border border-slate-300 p-3">

                        <button class="mt-3 w-full rounded-lg bg-amber-500 px-4 py-3 font-bold text-white">
                            + Dias
                        </button>
                    </form>

                    {{-- Ativar assinatura --}}
                    <form action="{{ route('admin.clientes.assinatura.ativar', $account) }}" method="POST"
                        class="rounded-xl border border-slate-200 p-4">
                        @csrf

                        <p class="font-bold">Assinatura</p>

                        <p class="mt-3 text-sm text-slate-500">
                            Transformar trial em cliente ativo.
                        </p>

                        <button class="mt-6 w-full rounded-lg bg-green-500 px-4 py-3 font-bold text-white">
                            Ativar assinatura
                        </button>
                    </form>

                    {{-- Alterar plano --}}
                    <form action="{{ route('admin.clientes.assinatura.plano', $account) }}" method="POST"
                        class="rounded-xl border border-slate-200 p-4">
                        @csrf
                        @method('PUT')

                        <p class="font-bold">Alterar plano</p>

                        <select name="plan_id" class="mt-3 w-full rounded-lg border border-slate-300 p-3">
                            @foreach($plans as $itemPlan)
                                <option value="{{ $itemPlan->id }}" @selected($plan?->id === $itemPlan->id)>
                                    {{ $itemPlan->nome }}
                                    — R$ {{ number_format($itemPlan->valor, 2, ',', '.') }}
                                </option>
                            @endforeach
                        </select>

                        <button class="mt-3 w-full rounded-lg bg-blue-600 px-4 py-3 font-bold text-white">
                            Alterar plano
                        </button>
                    </form>

                    {{-- Suspender / Reativar --}}
                    <form action="{{ route('admin.clientes.status', $account) }}" method="POST"
                        class="rounded-xl border border-slate-200 p-4">
                        @csrf
                        @method('PATCH')

                        <p class="font-bold">Status da conta</p>

                        <p class="mt-3 text-sm text-slate-500">
                            Atualmente:
                            <strong>
                                {{ $account->ativo ? 'Ativa' : 'Suspensa' }}
                            </strong>
                        </p>

                        <button class="mt-6 w-full rounded-lg px-4 py-3 font-bold text-white
                    {{ $account->ativo ? 'bg-red-500' : 'bg-green-500' }}">
                            {{ $account->ativo ? 'Suspender conta' : 'Reativar conta' }}
                        </button>
                    </form>

                </div>


            </section>


        
            {{-- SERVIÇOS RIMATECH --}}
            <section class="mt-6 rounded-2xl bg-white p-6 shadow-sm">

                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-lg font-black">🧩 Serviços Rimatech</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Serviços comerciais contratados por este cliente.
                        </p>
                    </div>

                    <span class="text-sm font-bold text-slate-500">
                        {{ $clienteServicos->count() }} serviço(s)
                    </span>
                </div>

                <div class="mt-6 space-y-4">
                    @forelse($clienteServicos as $clienteServico)
                        <article class="rounded-2xl border border-slate-200 p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h4 class="text-lg font-black">
                                            {{ $clienteServico->servico?->nome ?? 'Serviço' }}
                                        </h4>

                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                                            {{ ucfirst($clienteServico->status) }}
                                        </span>
                                    </div>

                                    <div class="mt-3 flex flex-wrap gap-x-6 gap-y-2 text-sm text-slate-600">
                                        <span>
                                            <strong>Valor:</strong>
                                            {{ $clienteServico->valor !== null ? 'R$ '.number_format((float) $clienteServico->valor, 2, ',', '.') : '—' }}
                                        </span>

                                        <span>
                                            <strong>Cobrança:</strong>
                                            {{ $clienteServico->tipo_cobranca === 'mensal' ? 'Mensal' : ($clienteServico->tipo_cobranca === 'anual' ? 'Anual' : 'Única') }}
                                        </span>

                                        <span>
                                            <strong>Início:</strong>
                                            {{ $clienteServico->data_inicio?->format('d/m/Y') ?? '—' }}
                                        </span>

                                        @if($clienteServico->data_fim)
                                            <span>
                                                <strong>Fim:</strong>
                                                {{ $clienteServico->data_fim->format('d/m/Y') }}
                                            </span>
                                        @endif
                                    </div>

                                    @if($clienteServico->observacoes)
                                        <p class="mt-3 text-sm text-slate-500">
                                            {{ $clienteServico->observacoes }}
                                        </p>
                                    @endif
                                </div>

                                <form
                                    action="{{ route('admin.clientes.servicos.destroy', [$account, $clienteServico]) }}"
                                    method="POST"
                                    onsubmit="return confirm('Remover este serviço do cliente?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="rounded-lg bg-red-50 px-4 py-2 text-sm font-bold text-red-600 hover:bg-red-100"
                                    >
                                        Remover
                                    </button>
                                </form>
                            </div>

                            <details class="mt-5 rounded-xl bg-slate-50 p-4">
                                <summary class="cursor-pointer font-bold text-slate-700">
                                    Editar serviço
                                </summary>

                                <form
                                    action="{{ route('admin.clientes.servicos.update', [$account, $clienteServico]) }}"
                                    method="POST"
                                    class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4"
                                >
                                    @csrf
                                    @method('PUT')

                                    <div>
                                        <label class="text-sm font-bold">Status</label>
                                        <select name="status" class="mt-1 w-full rounded-lg border border-slate-300 p-3">
                                            <option value="ativo" @selected($clienteServico->status === 'ativo')>Ativo</option>
                                            <option value="pausado" @selected($clienteServico->status === 'pausado')>Pausado</option>
                                            <option value="concluido" @selected($clienteServico->status === 'concluido')>Concluído</option>
                                            <option value="cancelado" @selected($clienteServico->status === 'cancelado')>Cancelado</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="text-sm font-bold">Valor</label>
                                        <input
                                            type="number"
                                            name="valor"
                                            min="0"
                                            step="0.01"
                                            value="{{ $clienteServico->valor }}"
                                            class="mt-1 w-full rounded-lg border border-slate-300 p-3"
                                        >
                                    </div>

                                    <div>
                                        <label class="text-sm font-bold">Cobrança</label>
                                        <select name="tipo_cobranca" class="mt-1 w-full rounded-lg border border-slate-300 p-3">
                                            <option value="mensal" @selected($clienteServico->tipo_cobranca === 'mensal')>Mensal</option>
                                            <option value="unico" @selected($clienteServico->tipo_cobranca === 'unico')>Única</option>
                                            <option value="anual" @selected($clienteServico->tipo_cobranca === 'anual')>Anual</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="text-sm font-bold">Data de início</label>
                                        <input
                                            type="date"
                                            name="data_inicio"
                                            value="{{ $clienteServico->data_inicio?->format('Y-m-d') }}"
                                            class="mt-1 w-full rounded-lg border border-slate-300 p-3"
                                        >
                                    </div>

                                    <div>
                                        <label class="text-sm font-bold">Data de término</label>
                                        <input
                                            type="date"
                                            name="data_fim"
                                            value="{{ $clienteServico->data_fim?->format('Y-m-d') }}"
                                            class="mt-1 w-full rounded-lg border border-slate-300 p-3"
                                        >
                                    </div>

                                    <div class="md:col-span-2 xl:col-span-3">
                                        <label class="text-sm font-bold">Observações</label>
                                        <input
                                            type="text"
                                            name="observacoes"
                                            value="{{ $clienteServico->observacoes }}"
                                            class="mt-1 w-full rounded-lg border border-slate-300 p-3"
                                        >
                                    </div>

                                    <div class="flex items-end">
                                        <button
                                            class="w-full rounded-lg bg-blue-600 px-4 py-3 font-bold text-white hover:bg-blue-700"
                                        >
                                            Salvar alterações
                                        </button>
                                    </div>
                                </form>
                            </details>
                        </article>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-300 px-6 py-10 text-center">
                            <p class="font-bold text-slate-700">
                                Nenhum serviço Rimatech vinculado ainda.
                            </p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-8 border-t border-slate-100 pt-6">
                    <h4 class="font-black">+ Adicionar serviço</h4>

                    <form
                        action="{{ route('admin.clientes.servicos.store', $account) }}"
                        method="POST"
                        class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4"
                    >
                        @csrf

                        <div>
                            <label class="text-sm font-bold">Serviço</label>

                            <select
                                name="servico_id"
                                class="mt-1 w-full rounded-lg border border-slate-300 p-3"
                                required
                            >
                                <option value="">Selecione...</option>

                                @foreach($servicosDisponiveis as $servico)
                                    <option value="{{ $servico->id }}">
                                        {{ $servico->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-bold">Valor</label>

                            <input
                                type="number"
                                name="valor"
                                min="0"
                                step="0.01"
                                class="mt-1 w-full rounded-lg border border-slate-300 p-3"
                            >
                        </div>

                        <div>
                            <label class="text-sm font-bold">Cobrança</label>

                            <select
                                name="tipo_cobranca"
                                class="mt-1 w-full rounded-lg border border-slate-300 p-3"
                                required
                            >
                                <option value="mensal">Mensal</option>
                                <option value="unico">Única</option>
                                <option value="anual">Anual</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-bold">Status</label>

                            <select
                                name="status"
                                class="mt-1 w-full rounded-lg border border-slate-300 p-3"
                                required
                            >
                                <option value="ativo">Ativo</option>
                                <option value="pausado">Pausado</option>
                                <option value="concluido">Concluído</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-bold">Data de início</label>

                            <input
                                type="date"
                                name="data_inicio"
                                value="{{ now()->format('Y-m-d') }}"
                                class="mt-1 w-full rounded-lg border border-slate-300 p-3"
                            >
                        </div>

                        <div>
                            <label class="text-sm font-bold">Data de término</label>

                            <input
                                type="date"
                                name="data_fim"
                                class="mt-1 w-full rounded-lg border border-slate-300 p-3"
                            >
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-sm font-bold">Observações</label>

                            <input
                                type="text"
                                name="observacoes"
                                placeholder="Observações comerciais, detalhes do projeto..."
                                class="mt-1 w-full rounded-lg border border-slate-300 p-3"
                            >
                        </div>

                        <div class="md:col-span-2 xl:col-span-4">
                            <button
                                class="rounded-xl bg-orange-500 px-6 py-3 font-bold text-white hover:bg-orange-600"
                            >
                                Adicionar serviço
                            </button>
                        </div>
                    </form>
                </div>
            </section>


        </main>

    </div>

</body>

</html>