<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comece seu teste grátis | Rima Food</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-900">
<main class="min-h-screen px-4 py-6 sm:px-6 sm:py-8">
<div class="mx-auto grid w-full max-w-7xl grid-cols-1 gap-6 lg:grid-cols-[1.08fr_.92fr]">
<section class="rounded-3xl bg-white p-5 shadow-2xl sm:p-8">
    <p class="text-sm font-bold uppercase tracking-[.18em] text-orange-500">15 dias grátis</p>
    <h1 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">Vamos preparar seu negócio.</h1>
    <p class="mt-3 text-slate-500">Responda algumas perguntas e o Rima Food cria seu ambiente automaticamente.</p>

    @if($errors->any())
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-700">
            <strong>Revise os dados:</strong>
            <ul class="mt-2 list-inside list-disc text-sm">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form id="onboardingForm" action="{{ route('saas.cadastro.store') }}" method="POST" class="mt-7">
        @csrf
        <div class="mb-7 flex items-center gap-2">
            @for($passo = 1; $passo <= 4; $passo++)
                <div class="step-dot h-2 flex-1 rounded-full bg-slate-200" data-step-dot="{{ $passo }}"></div>
            @endfor
        </div>

        <section class="step-panel" data-step="1">
            <p class="text-sm font-bold text-orange-600">Etapa 1 de 4</p>
            <h2 class="mt-1 text-2xl font-black">Conte sobre seu negócio</h2>
            <div class="mt-6 space-y-5">
                <div>
                    <label class="text-sm font-bold">Nome do estabelecimento</label>
                    <input id="restauranteNome" name="restaurante_nome" value="{{ old('restaurante_nome') }}" placeholder="Ex.: Pizzaria do Amir" class="mt-1 w-full rounded-xl border border-slate-300 p-3.5 focus:border-orange-500 focus:ring-orange-100" required>
                </div>
                <div>
                    <label class="text-sm font-bold">Qual é o segmento?</label>
                    <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach($segmentos as $slug => $segmento)
                            <label class="segmento-card cursor-pointer rounded-2xl border-2 border-slate-200 p-3 transition hover:border-orange-300"
                                data-nome="{{ $segmento['nome'] }}"
                                data-banner="{{ $segmento['banner'] }}"
                                data-cor="{{ $segmento['cor_primaria'] }}"
                                data-texto="{{ $segmento['texto'] }}">
                                <input type="radio" name="segmento" value="{{ $slug }}" class="sr-only" @checked(old('segmento') === $slug) required>
                                <span class="block text-2xl">{{ $segmento['icone'] }}</span>
                                <span class="mt-2 block text-sm font-bold">{{ $segmento['nome'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="step-panel hidden" data-step="2">
            <p class="text-sm font-bold text-orange-600">Etapa 2 de 4</p>
            <h2 class="mt-1 text-2xl font-black">Escolha seu plano</h2>
            <p class="mt-2 text-slate-500">Todos incluem 15 dias grátis.</p>
            <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                @foreach($plans as $plan)
                    @php
                        $nomePlano = match($plan->slug) {
                            'starter' => 'Rima Menu',
                            'pro' => 'Rima Menu + IA',
                            'business' => 'Rima Food',
                            default => $plan->nome,
                        };
                    @endphp
                    <label class="plan-card relative cursor-pointer rounded-2xl border-2 border-slate-200 p-4 transition hover:border-orange-300">
                        <input type="radio" name="plan_slug" value="{{ $plan->slug }}" class="sr-only" @checked(old('plan_slug', 'pro') === $plan->slug) required>
                        @if($plan->slug === 'pro')<span class="absolute right-3 top-3 rounded-full bg-orange-100 px-2 py-1 text-[10px] font-black text-orange-700">MAIS ESCOLHIDO</span>@endif
                        <p class="pr-16 text-lg font-black">{{ $nomePlano }}</p>
                        <p class="mt-4 text-2xl font-black">R$ {{ number_format($plan->valor, 2, ',', '.') }}</p>
                        <p class="text-xs text-slate-400">{{ $plan->trial_dias }} dias grátis</p>
                    </label>
                @endforeach
            </div>
        </section>

        <section class="step-panel hidden" data-step="3">
            <p class="text-sm font-bold text-orange-600">Etapa 3 de 4</p>
            <h2 class="mt-1 text-2xl font-black">Seus dados de acesso</h2>
            <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div><label class="text-sm font-bold">Seu nome</label><input name="user_nome" value="{{ old('user_nome') }}" class="mt-1 w-full rounded-xl border border-slate-300 p-3.5" required></div>
                <div><label class="text-sm font-bold">WhatsApp</label><input name="telefone" value="{{ old('telefone') }}" class="mt-1 w-full rounded-xl border border-slate-300 p-3.5" required></div>
                <div class="md:col-span-2"><label class="text-sm font-bold">E-mail</label><input name="email" type="email" value="{{ old('email') }}" class="mt-1 w-full rounded-xl border border-slate-300 p-3.5" required></div>
                <div><label class="text-sm font-bold">Cidade</label><input name="cidade" value="{{ old('cidade') }}" class="mt-1 w-full rounded-xl border border-slate-300 p-3.5"></div>
                <div><label class="text-sm font-bold">Estado</label><input name="estado" maxlength="2" value="{{ old('estado') }}" placeholder="RS" class="mt-1 w-full rounded-xl border border-slate-300 p-3.5 uppercase"></div>
                <div><label class="text-sm font-bold">Senha</label><input name="password" type="password" minlength="8" class="mt-1 w-full rounded-xl border border-slate-300 p-3.5" required></div>
                <div><label class="text-sm font-bold">Confirme a senha</label><input name="password_confirmation" type="password" minlength="8" class="mt-1 w-full rounded-xl border border-slate-300 p-3.5" required></div>
                <div class="md:col-span-2"><label class="text-sm font-bold">CPF/CNPJ </label><input name="documento" value="{{ old('documento') }}" class="mt-1 w-full rounded-xl border border-slate-300 p-3.5"></div>
            </div>
        </section>

        <section class="step-panel hidden" data-step="4">
            <p class="text-sm font-bold text-orange-600">Etapa 4 de 4</p>
            <h2 class="mt-1 text-2xl font-black">Você já possui um cardápio?</h2>
            <p class="mt-2 text-slate-500">Podemos levar você direto para o Import Menu.</p>
            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <label class="cardapio-card cursor-pointer rounded-2xl border-2 border-slate-200 p-5"><input type="radio" name="possui_cardapio" value="sim" class="sr-only" @checked(old('possui_cardapio', 'sim') === 'sim') required><span class="text-3xl">📄</span><p class="mt-3 font-black">Sim, já tenho</p><p class="mt-1 text-sm text-slate-500">PDF, foto, Excel ou CSV.</p></label>
                <label class="cardapio-card cursor-pointer rounded-2xl border-2 border-slate-200 p-5"><input type="radio" name="possui_cardapio" value="nao" class="sr-only" @checked(old('possui_cardapio') === 'nao') required><span class="text-3xl">✍️</span><p class="mt-3 font-black">Vou criar depois</p><p class="mt-1 text-sm text-slate-500">Entre direto no dashboard.</p></label>
            </div>
            <div class="mt-6 rounded-2xl bg-orange-50 p-5"><p class="font-black text-orange-800">Seu teste começa agora</p><p class="mt-1 text-sm text-orange-700">15 dias grátis, sem cartão e sem compromisso.</p></div>
        </section>

        <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
            <button id="prevButton" type="button" class="hidden rounded-xl border border-slate-300 px-5 py-3 font-bold text-slate-700">Voltar</button>
            <button id="nextButton" type="button" class="ml-auto rounded-xl bg-orange-500 px-6 py-3 font-black text-white hover:bg-orange-600">Continuar</button>
            <button id="submitButton" type="submit" class="ml-auto hidden rounded-xl bg-orange-500 px-6 py-3 font-black text-white hover:bg-orange-600">Criar meu restaurante</button>
        </div>
    </form>
</section>

<aside class="lg:sticky lg:top-8 lg:self-start">
    <div class="overflow-hidden rounded-3xl bg-white shadow-2xl">
        <div id="bannerPreview" class="flex min-h-64 items-end bg-cover bg-center p-6" style="background-image:linear-gradient(180deg,transparent,rgba(15,23,42,.88)),url('/images/onboarding/banners/outro.svg')">
            <div><p id="previewSegmento" class="text-sm font-bold uppercase tracking-[.16em] text-orange-300">Seu segmento</p><h2 id="previewNome" class="mt-2 text-3xl font-black text-white">Seu estabelecimento</h2><p id="previewTexto" class="mt-2 max-w-md text-sm text-slate-200">Uma identidade inicial será criada automaticamente.</p></div>
        </div>
        <div class="p-6"><div class="flex items-center justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-[.16em] text-slate-400">Prévia automática</p><p class="mt-1 text-lg font-black">Sua marca começa pronta</p></div><div id="previewColor" class="h-12 w-12 rounded-2xl bg-orange-500"></div></div><ul class="mt-5 space-y-3 text-sm text-slate-600"><li>✓ Banner inicial do segmento</li><li>✓ Cores aplicadas automaticamente</li><li>✓ Plano com 15 dias grátis</li><li>✓ Acesso ao Import Menu</li></ul></div>
    </div>
</aside>
</div>
</main>

<script>
let currentStep=1;const totalSteps=4;const panels=[...document.querySelectorAll('.step-panel')];const dots=[...document.querySelectorAll('.step-dot')];const prevButton=document.getElementById('prevButton');const nextButton=document.getElementById('nextButton');const submitButton=document.getElementById('submitButton');
function showStep(step){currentStep=step;panels.forEach(p=>p.classList.toggle('hidden',Number(p.dataset.step)!==step));dots.forEach(d=>{const a=Number(d.dataset.stepDot)<=step;d.classList.toggle('bg-orange-500',a);d.classList.toggle('bg-slate-200',!a)});prevButton.classList.toggle('hidden',step===1);nextButton.classList.toggle('hidden',step===totalSteps);submitButton.classList.toggle('hidden',step!==totalSteps);window.scrollTo({top:0,behavior:'smooth'})}
function validateStep(){const panel=document.querySelector(`.step-panel[data-step="${currentStep}"]`);const fields=[...panel.querySelectorAll('input[required]')];for(const f of fields){if(!f.checkValidity()){f.reportValidity();return false}}for(const name of [...new Set(fields.filter(f=>f.type==='radio').map(f=>f.name))]){if(!panel.querySelector(`input[name="${name}"]:checked`)){alert('Escolha uma opção para continuar.');return false}}return true}
nextButton.addEventListener('click',()=>{if(validateStep())showStep(Math.min(totalSteps,currentStep+1))});prevButton.addEventListener('click',()=>showStep(Math.max(1,currentStep-1)));
const nome=document.getElementById('restauranteNome');nome.addEventListener('input',()=>document.getElementById('previewNome').textContent=nome.value.trim()||'Seu estabelecimento');
function paint(selector){document.querySelectorAll(selector).forEach(card=>{const input=card.querySelector('input');const update=()=>{document.querySelectorAll(selector).forEach(c=>{const i=c.querySelector('input');c.classList.toggle('border-orange-500',i.checked);c.classList.toggle('bg-orange-50',i.checked);c.classList.toggle('border-slate-200',!i.checked)})};input.addEventListener('change',update);update()})}
paint('.plan-card');paint('.cardapio-card');
document.querySelectorAll('.segmento-card').forEach(card=>{const input=card.querySelector('input');const select=()=>{paint('.segmento-card');if(!input.checked)return;document.getElementById('previewSegmento').textContent=card.dataset.nome;document.getElementById('previewTexto').textContent=card.dataset.texto;document.getElementById('previewColor').style.background=card.dataset.cor;document.getElementById('bannerPreview').style.backgroundImage=`linear-gradient(180deg,transparent,rgba(15,23,42,.88)),url('${card.dataset.banner}')`};input.addEventListener('change',select);if(input.checked)select()});
showStep(1);
</script>
</body>
</html>
