<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Plan;
use App\Models\Restaurante;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OnboardingService
{
    public function criarConta(array $dados): array
    {
        return DB::transaction(function () use ($dados) {
            $plan = Plan::where('slug', $dados['plan_slug'])->firstOrFail();
            $identidade = $this->identidadeSegmento($dados['segmento']);

            $account = Account::create([
                'nome' => $dados['account_nome'],
                'slug' => $this->gerarSlugUnicoAccount($dados['account_nome']),
                'email' => $dados['email'],
                'telefone' => $dados['telefone'] ?? null,
                'documento' => $dados['documento'] ?? null,
                'ativo' => true,
            ]);

            $user = User::create([
                'account_id' => $account->id,
                'name' => $dados['user_nome'],
                'email' => $dados['email'],
                'password' => Hash::make($dados['password']),
            ]);

            $subscription = Subscription::create([
                'account_id' => $account->id,
                'plan_id' => $plan->id,
                'status' => 'trial',
                'trial_ends_at' => now()->addDays($plan->trial_dias),
                'current_period_starts_at' => now(),
                'current_period_ends_at' => now()->addDays($plan->trial_dias),
            ]);

            $restaurante = Restaurante::create([
                'account_id' => $account->id,
                'user_id' => $user->id,
                'nome' => $dados['restaurante_nome'],
                'slug' => $this->gerarSlugUnicoRestaurante($dados['restaurante_nome']),
                'telefone' => $dados['telefone'] ?? null,
                'documento' => $dados['documento'] ?? null,
                'email' => $dados['email'],
                'cidade' => $dados['cidade'] ?? null,
                'estado' => strtoupper($dados['estado'] ?? '') ?: null,
                'ativo' => true,
                'delivery' => true,
                'retirada' => true,
                'consumo_local' => false,
                'quantidade_mesas' => 0,
                'plano' => $this->mapearPlanoRestaurante($plan->slug),
                'segmento' => $dados['segmento'],
                'banner' => $identidade['banner'],
                'cor_primaria' => $identidade['cor_primaria'],
                'cor_secundaria' => $identidade['cor_secundaria'],
                'onboarding_concluido' => true,
            ]);

            $restaurante->clientes()->create([
                'nome' => 'Balcão',
                'telefone' => '0000000000',
                'ativo' => true,
                'observacao' => 'Cliente padrão criado automaticamente no onboarding.',
            ]);

            if (method_exists($restaurante, 'configuracaoEntrega')) {
                $restaurante->configuracaoEntrega()->firstOrCreate([
                    'restaurante_id' => $restaurante->id,
                ], [
                    'ate_5km' => 0,
                    'ate_10km' => 0,
                    'acima_10km' => 0,
                ]);
            }

            $subscription->logs()->create([
                'evento' => 'trial_started',
                'descricao' => 'Trial iniciado automaticamente no onboarding inteligente.',
                'metadata' => [
                    'plan' => $plan->slug,
                    'trial_dias' => $plan->trial_dias,
                    'segmento' => $dados['segmento'],
                ],
            ]);

            return compact('account', 'user', 'subscription', 'restaurante');
        });
    }

    private function mapearPlanoRestaurante(string $slug): string
    {
        return match ($slug) {
            'starter' => 'MENU',
            'pro' => 'MENU_IA',
            'business' => 'FOOD',
            default => 'MENU',
        };
    }

    private function identidadeSegmento(string $segmento): array
    {
        $mapa = [
            'hamburgueria' => ['banner' => '/images/onboarding/banners/hamburgueria.svg', 'cor_primaria' => '#F97316', 'cor_secundaria' => '#111827'],
            'pizzaria' => ['banner' => '/images/onboarding/banners/pizzaria.svg', 'cor_primaria' => '#DC2626', 'cor_secundaria' => '#F59E0B'],
            'restaurante' => ['banner' => '/images/onboarding/banners/restaurante.svg', 'cor_primaria' => '#16A34A', 'cor_secundaria' => '#166534'],
            'lanchonete' => ['banner' => '/images/onboarding/banners/lanchonete.svg', 'cor_primaria' => '#F59E0B', 'cor_secundaria' => '#92400E'],
            'sushi' => ['banner' => '/images/onboarding/banners/sushi.svg', 'cor_primaria' => '#EF4444', 'cor_secundaria' => '#111827'],
            'acai' => ['banner' => '/images/onboarding/banners/acai.svg', 'cor_primaria' => '#7E22CE', 'cor_secundaria' => '#4C1D95'],
            'doceria' => ['banner' => '/images/onboarding/banners/doceria.svg', 'cor_primaria' => '#EC4899', 'cor_secundaria' => '#9D174D'],
            'marmitaria' => ['banner' => '/images/onboarding/banners/marmitaria.svg', 'cor_primaria' => '#EA580C', 'cor_secundaria' => '#7C2D12'],
            'churrasquinho' => ['banner' => '/images/onboarding/banners/churrasquinho.svg', 'cor_primaria' => '#B91C1C', 'cor_secundaria' => '#451A03'],
            'cafeteria' => ['banner' => '/images/onboarding/banners/cafeteria.svg', 'cor_primaria' => '#92400E', 'cor_secundaria' => '#451A03'],
            'padaria' => ['banner' => '/images/onboarding/banners/padaria.svg', 'cor_primaria' => '#D97706', 'cor_secundaria' => '#78350F'],
            'outro' => ['banner' => '/images/onboarding/banners/outro.svg', 'cor_primaria' => '#F97316', 'cor_secundaria' => '#111827'],
        ];

        return $mapa[$segmento] ?? $mapa['outro'];
    }

    private function gerarSlugUnicoAccount(string $nome): string
    {
        $baseSlug = Str::slug($nome);
        $slug = $baseSlug;
        $contador = 1;

        while (Account::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $contador++;
        }

        return $slug;
    }

    private function gerarSlugUnicoRestaurante(string $nome): string
    {
        $baseSlug = Str::slug($nome);
        $slug = $baseSlug;
        $contador = 1;

        while (Restaurante::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $contador++;
        }

        return $slug;
    }
}
