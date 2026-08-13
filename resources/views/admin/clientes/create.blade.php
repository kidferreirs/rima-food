<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Novo Cliente | Rimatech Admin</title>

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
                <a
                    href="{{ route('admin.clientes.index') }}"
                    class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-bold hover:bg-slate-700"
                >
                    ← Clientes
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button
                        type="submit"
                        class="rounded-xl bg-red-500 px-4 py-2 text-sm font-bold text-white hover:bg-red-600"
                    >
                        Sair
                    </button>
                </form>
            </div>

        </div>
    </header>

    <main class="mx-auto max-w-5xl px-6 py-8">

        <div class="mb-8">
            <p class="text-sm font-bold uppercase tracking-[0.15em] text-orange-500">
                Clientes
            </p>

            <h2 class="mt-1 text-3xl font-black">
                + Novo cliente
            </h2>

            <p class="mt-1 text-slate-500">
                Cadastre um cliente da Rimatech e já vincule o primeiro serviço contratado.
            </p>
        </div>

        @if($errors->any())
            <div class="mb-6 rounded-xl bg-red-100 px-4 py-3 text-red-700">
                <p class="font-bold">Confira os dados informados:</p>

                <ul class="mt-2 list-disc pl-5 text-sm">
                    @foreach($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('admin.clientes.store') }}"
            class="space-y-6"
        >
            @csrf

            <section class="rounded-2xl bg-white p-6 shadow-sm">

                <h3 class="text-lg font-black">
                    👤 Dados do cliente
                </h3>

                <div class="mt-6 grid gap-4 md:grid-cols-2">

                    <div class="md:col-span-2">
                        <label class="text-sm font-bold">
                            Nome / Empresa *
                        </label>

                        <input
                            name="nome"
                            value="{{ old('nome') }}"
                            required
                            class="mt-1 w-full rounded-xl border border-slate-300 p-3.5"
                            placeholder="Ex.: Bella Fisio"
                        >
                    </div>

                    <div>
                        <label class="text-sm font-bold">
                            E-mail
                        </label>

                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="mt-1 w-full rounded-xl border border-slate-300 p-3.5"
                        >
                    </div>

                    <div>
                        <label class="text-sm font-bold">
                            Telefone / WhatsApp
                        </label>

                        <input
                            name="telefone"
                            value="{{ old('telefone') }}"
                            class="mt-1 w-full rounded-xl border border-slate-300 p-3.5"
                        >
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-sm font-bold">
                            CPF / CNPJ
                        </label>

                        <input
                            name="documento"
                            value="{{ old('documento') }}"
                            class="mt-1 w-full rounded-xl border border-slate-300 p-3.5"
                        >
                    </div>

                </div>

            </section>

            <section class="rounded-2xl bg-white p-6 shadow-sm">

                <h3 class="text-lg font-black">
                    🧩 Serviço contratado
                </h3>

                <div class="mt-6 grid gap-4 md:grid-cols-2">

                    <div class="md:col-span-2">
                        <label class="text-sm font-bold">
                            Serviço *
                        </label>

                        <select
                            name="servico_id"
                            required
                            class="mt-1 w-full rounded-xl border border-slate-300 p-3.5"
                        >
                            <option value="">Selecione...</option>

                            @foreach($servicos as $servico)
                                <option
                                    value="{{ $servico->id }}"
                                    @selected((string) old('servico_id') === (string) $servico->id)
                                >
                                    {{ $servico->nome }}
                                    {{ $servico->recorrente ? '— recorrente' : '— venda única' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-bold">
                            Valor
                        </label>

                        <input
                            type="number"
                            name="valor"
                            min="0"
                            step="0.01"
                            value="{{ old('valor') }}"
                            placeholder="0,00"
                            class="mt-1 w-full rounded-xl border border-slate-300 p-3.5"
                        >
                    </div>

                    <div>
                        <label class="text-sm font-bold">
                            Tipo de cobrança *
                        </label>

                        <select
                            name="tipo_cobranca"
                            required
                            class="mt-1 w-full rounded-xl border border-slate-300 p-3.5"
                        >
                            <option value="mensal" @selected(old('tipo_cobranca', 'mensal') === 'mensal')>
                                Mensal
                            </option>

                            <option value="unico" @selected(old('tipo_cobranca') === 'unico')>
                                Única
                            </option>

                            <option value="anual" @selected(old('tipo_cobranca') === 'anual')>
                                Anual
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-bold">
                            Status *
                        </label>

                        <select
                            name="status"
                            required
                            class="mt-1 w-full rounded-xl border border-slate-300 p-3.5"
                        >
                            <option value="ativo" @selected(old('status', 'ativo') === 'ativo')>
                                Ativo
                            </option>

                            <option value="pausado" @selected(old('status') === 'pausado')>
                                Pausado
                            </option>

                            <option value="concluido" @selected(old('status') === 'concluido')>
                                Concluído
                            </option>

                            <option value="cancelado" @selected(old('status') === 'cancelado')>
                                Cancelado
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-bold">
                            Data de início
                        </label>

                        <input
                            type="date"
                            name="data_inicio"
                            value="{{ old('data_inicio', now()->format('Y-m-d')) }}"
                            class="mt-1 w-full rounded-xl border border-slate-300 p-3.5"
                        >
                    </div>

                    <div>
                        <label class="text-sm font-bold">
                            Data de término
                        </label>

                        <input
                            type="date"
                            name="data_fim"
                            value="{{ old('data_fim') }}"
                            class="mt-1 w-full rounded-xl border border-slate-300 p-3.5"
                        >
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-sm font-bold">
                            Observações
                        </label>

                        <textarea
                            name="observacoes"
                            rows="4"
                            class="mt-1 w-full rounded-xl border border-slate-300 p-3.5"
                            placeholder="Detalhes comerciais, escopo, prazo, observações..."
                        >{{ old('observacoes') }}</textarea>
                    </div>

                </div>

            </section>

            <div class="flex flex-wrap items-center justify-end gap-3">

                <a
                    href="{{ route('admin.clientes.index') }}"
                    class="rounded-xl border border-slate-300 px-6 py-3 font-bold text-slate-700 hover:bg-white"
                >
                    Cancelar
                </a>

                <button
                    class="rounded-xl bg-orange-500 px-6 py-3 font-bold text-white hover:bg-orange-600"
                >
                    Cadastrar cliente
                </button>

            </div>

        </form>

    </main>

</div>

</body>
</html>