<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\OnboardingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
    public function create()
    {
        $plans = Plan::where('ativo', true)
            ->where('slug', '!=', 'enterprise')
            ->orderBy('valor')
            ->get();

        return view('saas.cadastro', [
            'plans' => $plans,
            'segmentos' => $this->segmentos(),
        ]);
    }

    public function store(Request $request, OnboardingService $onboarding)
    {
        $dados = $request->validate([
            'plan_slug' => ['required', Rule::exists('plans', 'slug')->where('ativo', true), Rule::notIn(['enterprise'])],
            'segmento' => ['required', Rule::in(array_keys($this->segmentos()))],
            'user_nome' => ['required', 'string', 'max:255'],
            'restaurante_nome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'telefone' => ['required', 'string', 'max:50'],
            'cidade' => ['nullable', 'string', 'max:120'],
            'estado' => ['nullable', 'string', 'size:2'],
            'documento' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'possui_cardapio' => ['required', Rule::in(['sim', 'nao'])],
        ], [
            'plan_slug.required' => 'Escolha um plano.',
            'segmento.required' => 'Escolha o segmento do seu negócio.',
            'user_nome.required' => 'Informe seu nome.',
            'restaurante_nome.required' => 'Informe o nome do estabelecimento.',
            'email.required' => 'Informe seu e-mail.',
            'email.unique' => 'Este e-mail já possui uma conta.',
            'telefone.required' => 'Informe seu WhatsApp.',
            'password.required' => 'Crie uma senha.',
            'password.confirmed' => 'A confirmação da senha não confere.',
        ]);

        $dados['account_nome'] = $dados['restaurante_nome'];
        $resultado = $onboarding->criarConta($dados);
        Auth::login($resultado['user']);

        if ($dados['possui_cardapio'] === 'sim') {
            return redirect()
                ->route('restaurante.importacao.cardapio', $resultado['restaurante']->slug)
                ->with('success', 'Seu restaurante foi criado! Agora envie seu cardápio para a IA organizar.');
        }

        return redirect()
            ->route('restaurante.dashboard', $resultado['restaurante']->slug)
            ->with('success', 'Bem-vindo ao Rima Food! Seu teste grátis de 15 dias foi iniciado.');
    }

    private function segmentos(): array
    {
        return [
            'hamburgueria' => ['nome' => 'Hamburgueria', 'icone' => '🍔', 'banner' => '/images/onboarding/banners/hamburgueria.svg', 'cor_primaria' => '#F97316', 'texto' => 'Hambúrgueres, combos e acompanhamentos.'],
            'pizzaria' => ['nome' => 'Pizzaria', 'icone' => '🍕', 'banner' => '/images/onboarding/banners/pizzaria.svg', 'cor_primaria' => '#DC2626', 'texto' => 'Pizzas, bordas, sabores e tamanhos.'],
            'restaurante' => ['nome' => 'Restaurante', 'icone' => '🍽️', 'banner' => '/images/onboarding/banners/restaurante.svg', 'cor_primaria' => '#16A34A', 'texto' => 'Pratos, refeições e atendimento completo.'],
            'lanchonete' => ['nome' => 'Lanchonete', 'icone' => '🥪', 'banner' => '/images/onboarding/banners/lanchonete.svg', 'cor_primaria' => '#F59E0B', 'texto' => 'Lanches, porções e bebidas.'],
            'sushi' => ['nome' => 'Sushi', 'icone' => '🍣', 'banner' => '/images/onboarding/banners/sushi.svg', 'cor_primaria' => '#EF4444', 'texto' => 'Combos, peças, temakis e pratos orientais.'],
            'acai' => ['nome' => 'Açaí', 'icone' => '🫐', 'banner' => '/images/onboarding/banners/acai.svg', 'cor_primaria' => '#7E22CE', 'texto' => 'Açaí, cremes, tamanhos e complementos.'],
            'doceria' => ['nome' => 'Doceria e Bolos', 'icone' => '🍰', 'banner' => '/images/onboarding/banners/doceria.svg', 'cor_primaria' => '#EC4899', 'texto' => 'Bolos, doces, kits e encomendas.'],
            'marmitaria' => ['nome' => 'Marmitaria', 'icone' => '🍱', 'banner' => '/images/onboarding/banners/marmitaria.svg', 'cor_primaria' => '#EA580C', 'texto' => 'Marmitas, refeições e cardápios do dia.'],
            'churrasquinho' => ['nome' => 'Churrasquinho', 'icone' => '🍢', 'banner' => '/images/onboarding/banners/churrasquinho.svg', 'cor_primaria' => '#B91C1C', 'texto' => 'Espetos, porções, bebidas e acompanhamentos.'],
            'cafeteria' => ['nome' => 'Cafeteria', 'icone' => '☕', 'banner' => '/images/onboarding/banners/cafeteria.svg', 'cor_primaria' => '#92400E', 'texto' => 'Cafés, salgados, doces e combos.'],
            'padaria' => ['nome' => 'Padaria', 'icone' => '🥖', 'banner' => '/images/onboarding/banners/padaria.svg', 'cor_primaria' => '#D97706', 'texto' => 'Pães, salgados, doces e encomendas.'],
            'outro' => ['nome' => 'Outro negócio', 'icone' => '🍴', 'banner' => '/images/onboarding/banners/outro.svg', 'cor_primaria' => '#F97316', 'texto' => 'Uma identidade simples para seu negócio.'],
        ];
    }
}
