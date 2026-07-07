<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Cliente;
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
                'email' => $dados['email'],
                'ativo' => true,
                'delivery' => true,
                'retirada' => true,
                'consumo_local' => false,
                'quantidade_mesas' => 0,
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
                'descricao' => 'Trial iniciado automaticamente no onboarding.',
                'metadata' => [
                    'plan' => $plan->slug,
                    'trial_dias' => $plan->trial_dias,
                ],
            ]);

            return compact('account', 'user', 'subscription', 'restaurante');
        });
    }

    private function gerarSlugUnicoAccount(string $nome): string
    {
        $baseSlug = Str::slug($nome);
        $slug = $baseSlug;
        $contador = 1;

        while (Account::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $contador;
            $contador++;
        }

        return $slug;
    }

    private function gerarSlugUnicoRestaurante(string $nome): string
    {
        $baseSlug = Str::slug($nome);
        $slug = $baseSlug;
        $contador = 1;

        while (Restaurante::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $contador;
            $contador++;
        }

        return $slug;
    }
}