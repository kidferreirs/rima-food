<x-rimafood.layout>
@php
    $midia = function (?string $path) {
        if (!$path) return null;
        if (\Illuminate\Support\Str::startsWith($path, ['http://','https://','/images/','images/'])) {
            return asset(ltrim($path, '/'));
        }
        return \Illuminate\Support\Facades\Storage::url($path);
    };
    $logoUrl = $midia($restaurante->logo);
    $bannerUrl = $midia($restaurante->banner);
@endphp

<div class="mx-auto max-w-6xl p-4 sm:p-6 lg:p-8">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-[.16em] text-orange-500">Central da sua loja</p>
            <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">Meu Restaurante</h1>
            <p class="mt-2 text-slate-500">Edite identidade, delivery, horários e informações gerais.</p>
        </div>
        <a href="{{ $linkMenu }}" target="_blank" class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-center font-bold text-slate-700">Abrir cardápio</a>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 p-4 font-semibold text-green-700">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-700">
            <strong>Revise os dados:</strong>
            <ul class="mt-2 list-inside list-disc text-sm">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="flex gap-2 overflow-x-auto border-b border-slate-200 p-3">
            @foreach(['geral'=>'🏪 Geral','identidade'=>'🎨 Identidade','delivery'=>'🚚 Delivery','horarios'=>'🕒 Horários','contatos'=>'📱 Contatos'] as $slug=>$label)
                <button type="button" data-tab="{{ $slug }}" class="tab-btn shrink-0 rounded-xl px-4 py-2.5 text-sm font-bold {{ $loop->first ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">{{ $label }}</button>
            @endforeach
        </div>

        <form action="{{ route('restaurante.meu-restaurante.update', $restaurante->slug) }}" method="POST" enctype="multipart/form-data" class="p-5 sm:p-8">
            @csrf
            @method('PUT')

            <section data-panel="geral" class="tab-panel">
                <h2 class="text-2xl font-black">Informações gerais</h2>
                <p class="mt-1 text-slate-500">Dados principais do estabelecimento.</p>
                <div class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="md:col-span-2"><label class="text-sm font-bold">Nome do restaurante</label><input name="nome" value="{{ old('nome',$restaurante->nome) }}" class="mt-1 w-full rounded-xl border border-slate-300 p-3.5" required></div>
                    <div><label class="text-sm font-bold">CPF ou CNPJ</label><input id="documento" name="documento" maxlength="18" value="{{ old('documento',$restaurante->documento) }}" oninput="mascaraDocumento(this)" class="mt-1 w-full rounded-xl border border-slate-300 p-3.5" required></div>
                    <div><label class="text-sm font-bold">Telefone ou WhatsApp</label><input name="telefone" value="{{ old('telefone',$restaurante->telefone) }}" class="mt-1 w-full rounded-xl border border-slate-300 p-3.5" required></div>
                    <div class="md:col-span-2"><label class="text-sm font-bold">E-mail</label><input type="email" name="email" value="{{ old('email',$restaurante->email) }}" class="mt-1 w-full rounded-xl border border-slate-300 p-3.5" required></div>
                    <div><label class="text-sm font-bold">CEP</label><input id="cep" name="cep" value="{{ old('cep',$restaurante->cep) }}" class="mt-1 w-full rounded-xl border border-slate-300 p-3.5"></div>
                    <div><label class="text-sm font-bold">Endereço</label><input id="endereco" name="endereco" value="{{ old('endereco',$restaurante->endereco) }}" class="mt-1 w-full rounded-xl border border-slate-300 p-3.5"></div>
                    <div><label class="text-sm font-bold">Número</label><input id="numero" name="numero" value="{{ old('numero',$restaurante->numero) }}" class="mt-1 w-full rounded-xl border border-slate-300 p-3.5"></div>
                    <div><label class="text-sm font-bold">Complemento</label><input name="complemento" value="{{ old('complemento',$restaurante->complemento) }}" class="mt-1 w-full rounded-xl border border-slate-300 p-3.5"></div>
                    <div><label class="text-sm font-bold">Bairro</label><input id="bairro" name="bairro" value="{{ old('bairro',$restaurante->bairro) }}" class="mt-1 w-full rounded-xl border border-slate-300 p-3.5"></div>
                    <div><label class="text-sm font-bold">Cidade</label><input id="cidade" name="cidade" value="{{ old('cidade',$restaurante->cidade) }}" class="mt-1 w-full rounded-xl border border-slate-300 p-3.5"></div>
                    <div><label class="text-sm font-bold">Estado</label><input id="estado" name="estado" value="{{ old('estado',$restaurante->estado) }}" class="mt-1 w-full rounded-xl border border-slate-300 p-3.5 uppercase"></div>
                </div>
            </section>

            <section data-panel="identidade" class="tab-panel hidden">
                <h2 class="text-2xl font-black">Identidade visual</h2>
                <p class="mt-1 text-slate-500">Logo e banner exibidos no cardápio público.</p>
                <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 p-5">
                        <p class="font-black">Logo</p>
                        @if($logoUrl)
                            <div class="mt-4 flex h-44 items-center justify-center overflow-hidden rounded-2xl bg-slate-50 p-4"><img src="{{ $logoUrl }}" class="max-h-full max-w-full object-contain" alt="Logo atual"></div>
                            <p class="mt-3 text-sm text-slate-500">Escolha um novo arquivo para trocar.</p>
                        @else
                            <div class="mt-4 flex h-44 flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 text-center">
                                <span class="text-3xl">＋</span><p class="mt-2 font-bold text-slate-700">Adicionar logo</p><p class="text-sm text-slate-400">Ela fica oculta até você enviar.</p>
                            </div>
                        @endif
                        <input type="file" name="logo" accept="image/*" class="mt-4 w-full rounded-xl border border-slate-300 p-3">
                    </div>

                    <div class="rounded-2xl border border-slate-200 p-5">
                        <p class="font-black">Banner</p>
                        @if($bannerUrl)
                            <div class="mt-4 h-44 overflow-hidden rounded-2xl bg-slate-100"><img src="{{ $bannerUrl }}" class="h-full w-full object-cover" alt="Banner atual"></div>
                        @else
                            <div class="mt-4 flex h-44 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-500 to-slate-900 font-black text-white">Banner ainda não definido</div>
                        @endif
                        <p class="mt-3 text-sm text-slate-500">O banner automático do onboarding deve aparecer aqui. Você pode substituí-lo.</p>
                        <input type="file" name="banner" accept="image/*" class="mt-4 w-full rounded-xl border border-slate-300 p-3">
                    </div>
                </div>
            </section>

            <section data-panel="delivery" class="tab-panel hidden">
                <h2 class="text-2xl font-black">Atendimento e delivery</h2>
                <p class="mt-1 text-slate-500">Escolha como atende e defina o preço da entrega.</p>
                <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <label class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4"><input id="delivery" type="checkbox" name="delivery" value="1" @checked(old('delivery',$restaurante->delivery))><span class="font-bold">🚚 Delivery</span></label>
                    <label class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4"><input type="checkbox" name="retirada" value="1" @checked(old('retirada',$restaurante->retirada))><span class="font-bold">🛍️ Retirada</span></label>
                    <label class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4"><input id="consumoLocal" type="checkbox" name="consumo_local" value="1" @checked(old('consumo_local',$restaurante->consumo_local))><span class="font-bold">🍽️ Consumo local</span></label>
                </div>
                <div id="deliveryFields" class="mt-5 rounded-2xl bg-slate-50 p-5 {{ old('delivery',$restaurante->delivery) ? '' : 'hidden' }}">
                    <div class="flex flex-col gap-1">
                        <h3 class="font-black text-slate-900">Taxas por distância</h3>
                        <p class="text-sm text-slate-500">Defina quanto será cobrado conforme a distância da entrega.</p>
                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-3">
                        <div>
                            <label class="text-sm font-bold">Até 5 km</label>
                            <div class="relative mt-1">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-500">R$</span>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="ate_5km"
                                    value="{{ old('ate_5km', $configuracaoEntrega->ate_5km) }}"
                                    class="delivery-input w-full rounded-xl border border-slate-300 py-3.5 pl-12 pr-4"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-bold">De 5 a 10 km</label>
                            <div class="relative mt-1">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-500">R$</span>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="ate_10km"
                                    value="{{ old('ate_10km', $configuracaoEntrega->ate_10km) }}"
                                    class="delivery-input w-full rounded-xl border border-slate-300 py-3.5 pl-12 pr-4"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-bold">Acima de 10 km</label>
                            <div class="relative mt-1">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-500">R$</span>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="acima_10km"
                                    value="{{ old('acima_10km', $configuracaoEntrega->acima_10km) }}"
                                    class="delivery-input w-full rounded-xl border border-slate-300 py-3.5 pl-12 pr-4"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="mt-5">
                        <label class="text-sm font-bold">Tempo médio</label>
                        <div class="relative mt-1">
                            <input
                                type="number"
                                min="1"
                                max="600"
                                name="tempo_medio"
                                value="{{ old('tempo_medio',$restaurante->tempo_medio) }}"
                                class="delivery-input w-full rounded-xl border border-slate-300 p-3.5 pr-24"
                            >
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm text-slate-500">minutos</span>
                        </div>
                    </div>

                    <p class="mt-3 text-xs text-slate-400">Use 0,00 em uma faixa para entrega grátis.</p>
                </div>

                <div id="mesasFields" class="mt-5 rounded-2xl bg-slate-50 p-5 {{ old('consumo_local',$restaurante->consumo_local) ? '' : 'hidden' }}">
                    <label class="text-sm font-bold">Quantidade de mesas</label>
                    <input
                        id="quantidadeMesas"
                        type="number"
                        name="quantidade_mesas"
                        min="1"
                        max="500"
                        value="{{ old('quantidade_mesas', $restaurante->consumo_local ? max(1, (int) $restaurante->quantidade_mesas) : 0) }}"
                        class="mt-1 w-full rounded-xl border border-slate-300 p-3.5"
                        @disabled(!old('consumo_local', $restaurante->consumo_local))
                    >
                </div>
            </section>

            <section data-panel="horarios" class="tab-panel hidden">
                <h2 class="text-2xl font-black">Horário de atendimento</h2>
                <p class="mt-1 text-slate-500">Nesta versão, use um horário geral.</p>
                <div class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div><label class="text-sm font-bold">Abre às</label><input type="time" name="abre_as" value="{{ old('abre_as',$restaurante->abre_as ? \Carbon\Carbon::parse($restaurante->abre_as)->format('H:i') : '') }}" class="mt-1 w-full rounded-xl border border-slate-300 p-3.5"></div>
                    <div><label class="text-sm font-bold">Fecha às</label><input type="time" name="fecha_as" value="{{ old('fecha_as',$restaurante->fecha_as ? \Carbon\Carbon::parse($restaurante->fecha_as)->format('H:i') : '') }}" class="mt-1 w-full rounded-xl border border-slate-300 p-3.5"></div>
                </div>
            </section>

            <section data-panel="contatos" class="tab-panel hidden">
                <h2 class="text-2xl font-black">Contatos e presença digital</h2>
                <p class="mt-1 text-slate-500">Links usados na divulgação do restaurante.</p>
                <div class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div><label class="text-sm font-bold">Instagram</label><input name="instagram" value="{{ old('instagram',$restaurante->instagram) }}" placeholder="@seurestaurante" class="mt-1 w-full rounded-xl border border-slate-300 p-3.5"></div>
                    <div><label class="text-sm font-bold">Site</label><input name="site" type="url" value="{{ old('site',$restaurante->site) }}" placeholder="https://..." class="mt-1 w-full rounded-xl border border-slate-300 p-3.5"></div>
                    <div class="md:col-span-2"><label class="text-sm font-bold">Link do cardápio</label><div class="mt-1 flex flex-col gap-2 sm:flex-row"><input id="linkMenu" value="{{ $linkMenu }}" readonly class="min-w-0 flex-1 rounded-xl border border-slate-300 bg-slate-50 p-3.5"><button type="button" onclick="copiarLink()" class="rounded-xl bg-slate-900 px-5 py-3 font-bold text-white">Copiar</button></div></div>
                </div>
            </section>

            <div class="mt-8 flex justify-end border-t border-slate-200 pt-6"><button class="rounded-xl bg-orange-500 px-6 py-3 font-black text-white hover:bg-orange-600">Salvar alterações</button></div>
        </form>
    </div>
</div>

<script>
const tabs=document.querySelectorAll('.tab-btn');const panels=document.querySelectorAll('.tab-panel');
tabs.forEach(btn=>btn.addEventListener('click',()=>{const tab=btn.dataset.tab;tabs.forEach(b=>{const on=b.dataset.tab===tab;b.classList.toggle('bg-slate-900',on);b.classList.toggle('text-white',on);b.classList.toggle('text-slate-600',!on)});panels.forEach(p=>p.classList.toggle('hidden',p.dataset.panel!==tab));}));
const delivery = document.getElementById('delivery');
const deliveryFields = document.getElementById('deliveryFields');
const deliveryInputs = document.querySelectorAll('.delivery-input');

function atualizarCamposDelivery() {
    const ativo = Boolean(delivery?.checked);
    deliveryFields?.classList.toggle('hidden', !ativo);
    deliveryInputs.forEach(input => input.disabled = !ativo);
}

delivery?.addEventListener('change', atualizarCamposDelivery);
atualizarCamposDelivery();

const consumo = document.getElementById('consumoLocal');
const mesas = document.getElementById('mesasFields');
const quantidadeMesas = document.getElementById('quantidadeMesas');

function atualizarCamposMesas() {
    const ativo = Boolean(consumo?.checked);
    mesas?.classList.toggle('hidden', !ativo);

    if (quantidadeMesas) {
        quantidadeMesas.disabled = !ativo;

        if (ativo && Number(quantidadeMesas.value) < 1) {
            quantidadeMesas.value = 1;
        }

        if (!ativo) {
            quantidadeMesas.value = 0;
        }
    }
}

consumo?.addEventListener('change', atualizarCamposMesas);
atualizarCamposMesas();
document.getElementById('cep')?.addEventListener('blur',async function(){const cep=this.value.replace(/\D/g,'');if(cep.length!==8)return;const r=await fetch(`https://viacep.com.br/ws/${cep}/json/`);const d=await r.json();if(d.erro)return;document.getElementById('endereco').value=d.logradouro;document.getElementById('bairro').value=d.bairro;document.getElementById('cidade').value=d.localidade;document.getElementById('estado').value=d.uf;});
function mascaraDocumento(campo){let v=campo.value.replace(/\D/g,'');if(v.length<=11){v=v.replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d{1,2})$/,'$1-$2')}else{v=v.replace(/^(\d{2})(\d)/,'$1.$2').replace(/^(\d{2})\.(\d{3})(\d)/,'$1.$2.$3').replace(/\.(\d{3})(\d)/,'.$1/$2').replace(/(\d{4})(\d)/,'$1-$2')}campo.value=v.substring(0,18)}
function copiarLink(){navigator.clipboard.writeText(document.getElementById('linkMenu').value);alert('Link copiado!')}
</script>
</x-rimafood.layout>
