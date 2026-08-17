<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAssinaturaController extends Controller
{
    public function estenderTrial(
        Request $request,
        Account $account
    ): RedirectResponse {
        $dados = $request->validate([
            'dias' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $dias = (int) $dados['dias'];

        $subscription = $account->subscription;

        if (!$subscription) {
            return back()->with('error', 'Cliente sem assinatura.');
        }

        $base = $subscription->trial_ends_at?->isFuture()
            ? $subscription->trial_ends_at
            : now();

        $subscription->update([
            'status' => 'trial',
            'trial_ends_at' => $base->copy()->addDays($dias),
            'current_period_ends_at' => $base->copy()->addDays($dias),
        ]);

        $subscription->logs()->create([
            'evento' => 'trial_extended',
            'descricao' => 'Trial estendido manualmente pelo Rimatech Admin.',
            'metadata' => [
                'dias' => $dados['dias'],
                'admin_user_id' => auth()->id(),
            ],
        ]);

        return back()->with('success', 'Trial estendido com sucesso.');
    }

    public function ativar(Account $account): RedirectResponse
    {
        $subscription = $account->subscription;

        if (!$subscription) {
            return back()->with('error', 'Cliente sem assinatura.');
        }

        $subscription->update([
            'status' => 'active',
            'current_period_starts_at' => now(),
            'current_period_ends_at' => now()->addMonth(),
            'cancelled_at' => null,
        ]);

        $subscription->logs()->create([
            'evento' => 'subscription_activated',
            'descricao' => 'Assinatura ativada manualmente pelo Rimatech Admin.',
            'metadata' => [
                'admin_user_id' => auth()->id(),
            ],
        ]);

        return back()->with('success', 'Assinatura ativada com sucesso.');
    }

    public function alterarPlano(
        Request $request,
        Account $account
    ): RedirectResponse {
        $dados = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $subscription = $account->subscription;

        if (!$subscription) {
            return back()->with(
                'error',
                'Cliente sem assinatura.'
            );
        }

        $plan = Plan::findOrFail($dados['plan_id']);

        $planoRestaurante = match ($plan->slug) {
            'menu' => 'MENU',
            'menu-ia' => 'MENU_IA',
            'food' => 'FOOD',
            default => 'MENU',
        };

        DB::transaction(function () use ($subscription, $account, $plan, $planoRestaurante) {
            /*
            |--------------------------------------------------------------------------
            | Assinatura SaaS
            |--------------------------------------------------------------------------
            */

            $subscription->update([
                'plan_id' => $plan->id,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Restaurante
            |--------------------------------------------------------------------------
            |
            | Mantemos restaurantes.plano sincronizado porque o sistema ainda
            | utiliza esse campo para liberar recursos e montar o menu lateral.
            |
            */

            $account->restaurantes()->update([
                'plano' => $planoRestaurante,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Histórico
            |--------------------------------------------------------------------------
            */

            $subscription->logs()->create([
                'evento' => 'plan_changed',
                'descricao' =>
                    'Plano alterado manualmente pelo Rimatech Admin.',
                'metadata' => [
                    'plan_id' => $plan->id,
                    'plan_slug' => $plan->slug,
                    'plano_restaurante' => $planoRestaurante,
                    'admin_user_id' => auth()->id(),
                ],
            ]);
        });

        return back()->with(
            'success',
            "Plano alterado para {$plan->nome} com sucesso."
        );
    }

    public function alterarStatusConta(
        Account $account
    ): RedirectResponse {
        $account->update([
            'ativo' => !$account->ativo,
        ]);

        return back()->with(
            'success',
            $account->ativo
            ? 'Conta reativada com sucesso.'
            : 'Conta suspensa com sucesso.'
        );
    }
}