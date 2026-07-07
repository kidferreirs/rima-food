<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comece seu teste grátis | Rima Food</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-950 min-h-screen text-slate-900">

    <main class="min-h-screen flex items-start justify-center px-6 py-8">

        <div class="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">

            <section class="bg-white rounded-3xl shadow-2xl p-8 w-full">

                <h2 class="text-3xl font-extrabold mb-2">
                    🍔 Começar agora
                </h2>

                <p class="text-slate-500 mb-8">
                    Seu ambiente será criado automaticamente.
                </p>

                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mb-6">
                        <strong>Ops!</strong>
                        <ul class="mt-2 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('saas.cadastro.store') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-2">
                            @foreach($plans as $plan)
                                <label class="border rounded-2xl p-4 cursor-pointer hover:border-green-500 transition">
                                    <input type="radio" name="plan_slug" value="{{ $plan->slug }}" class="mb-3"
                                        @checked(old('plan_slug', 'starter') === $plan->slug)>

                                    <div class="font-extrabold">{{ $plan->nome }}</div>

                                    <div class="text-green-600 font-bold mt-1">
                                        R$ {{ number_format($plan->valor, 2, ',', '.') }}
                                    </div>

                                    <div class="text-xs text-slate-400 mt-1">
                                        {{ $plan->trial_dias }} dias grátis
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <label class="font-bold text-sm">Plano</label>
                    <div>
                        <label class="text-sm font-semibold">
                            Seu nome <span class="text-red-600">*</span>
                        </label>
                        <input name="user_nome" value="{{ old('user_nome') }}" placeholder="Seu nome"
                            class="w-full border rounded-xl p-3 mt-1" required
                            oninvalid="this.setCustomValidity('Coloque seu nome.')"
                            oninput="this.setCustomValidity('')">
                    </div>

                    <div>
                        <label class="text-sm font-semibold">
                            Nome do estabelecimento <span class="text-red-600">*</span>
                        </label>
                        <input name="restaurante_nome" value="{{ old('restaurante_nome') }}"
                            placeholder="Nome do estabelecimento" class="w-full border rounded-xl p-3 mt-1" required
                            oninvalid="this.setCustomValidity('Coloque o nome do estabelecimento.')"
                            oninput="this.setCustomValidity('')">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold">
                                E-mail <span class="text-red-600">*</span>
                            </label>
                            <input name="email" value="{{ old('email') }}" type="email" placeholder="E-mail"
                                class="w-full border rounded-xl p-3 mt-1" required
                                oninvalid="this.setCustomValidity('Coloque um e-mail válido.')"
                                oninput="this.setCustomValidity('')">
                        </div>

                        <div>
                            <label class="text-sm font-semibold">
                                Telefone <span class="text-red-600">*</span>
                            </label>
                            <input name="telefone" value="{{ old('telefone') }}" placeholder="Telefone"
                                class="w-full border rounded-xl p-3 mt-1" required
                                oninvalid="this.setCustomValidity('Coloque o seu telefone.')"
                                oninput="this.setCustomValidity('')">
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-semibold">
                            CPF/CNPJ <span class="text-red-600">*</span>
                        </label>
                        <input name="documento" value="{{ old('documento') }}" placeholder="CPF ou CNPJ"
                            class="w-full border rounded-xl p-3 mt-1" required
                            oninvalid="this.setCustomValidity('Preencha o CPF ou CNPJ.')"
                            oninput="this.setCustomValidity('')">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold">
                                Senha <span class="text-red-600">*</span>
                            </label>
                            <input name="password" type="password" placeholder="Mínimo 8 caracteres"
                                class="w-full border rounded-xl p-3 mt-1" required minlength="8"
                                oninvalid="this.setCustomValidity('A senha deve ter no mínimo 8 caracteres.')"
                                oninput="this.setCustomValidity('')">
                        </div>

                        <div>
                            <label class="text-sm font-semibold">
                                Confirme a senha <span class="text-red-600">*</span>
                            </label>
                            <input name="password_confirmation" type="password" placeholder="Confirmar senha"
                                class="w-full border rounded-xl p-3 mt-1" required minlength="8"
                                oninvalid="this.setCustomValidity('Confirme sua senha.')"
                                oninput="this.setCustomValidity('')">
                        </div>
                    </div>

                    <p class="text-sm text-slate-500">
                        <span class="text-red-600 font-bold">*</span> campos obrigatórios
                    </p>

                    <button
                        class="w-full bg-green-500 hover:bg-green-600 text-white font-extrabold py-4 rounded-2xl transition">
                        🚀 Criar meu restaurante
                    </button>
                </form>
            </section>
        </div>
    </main>
</body>

</html>