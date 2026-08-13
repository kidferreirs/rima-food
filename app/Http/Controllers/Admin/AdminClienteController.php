<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\ClienteServico;
use App\Models\Plan;
use App\Models\Servico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminClienteController extends Controller
{
    public function index(Request $request): View
    {
        $busca = trim((string) $request->get('busca', ''));
        $status = $request->get('status');
        $planId = $request->get('plan_id');
        $conta = $request->get('conta');

        $clientes = Account::query()
            ->with([
                'subscription.plan',
                'restaurantes',
            ])
            ->when($busca !== '', function ($query) use ($busca) {
                $query->where(function ($subquery) use ($busca) {
                    $subquery
                        ->where('nome', 'like', "%{$busca}%")
                        ->orWhere('email', 'like', "%{$busca}%")
                        ->orWhere('telefone', 'like', "%{$busca}%")
                        ->orWhereHas('restaurantes', function ($restauranteQuery) use ($busca) {
                            $restauranteQuery->where('nome', 'like', "%{$busca}%");
                        });
                });
            })
            ->when($status === 'trial', function ($query) {
                $query->whereHas('subscription', function ($subscriptionQuery) {
                    $subscriptionQuery
                        ->where('status', 'trial')
                        ->where('trial_ends_at', '>=', now());
                });
            })
            ->when($status === 'active', function ($query) {
                $query->whereHas('subscription', function ($subscriptionQuery) {
                    $subscriptionQuery->where('status', 'active');
                });
            })
            ->when($status === 'expired', function ($query) {
                $query->whereHas('subscription', function ($subscriptionQuery) {
                    $subscriptionQuery
                        ->where('status', 'trial')
                        ->whereNotNull('trial_ends_at')
                        ->where('trial_ends_at', '<', now());
                });
            })
            ->when($status === 'none', function ($query) {
                $query->whereDoesntHave('subscriptions');
            })
            ->when($planId, function ($query) use ($planId) {
                $query->whereHas('subscription', function ($subscriptionQuery) use ($planId) {
                    $subscriptionQuery->where('plan_id', $planId);
                });
            })
            ->when($conta === 'ativa', fn ($query) => $query->where('ativo', true))
            ->when($conta === 'suspensa', fn ($query) => $query->where('ativo', false))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $plans = Plan::where('ativo', true)
            ->orderBy('valor')
            ->get();

        return view('admin.clientes.index', compact(
            'clientes',
            'plans',
            'busca',
            'status',
            'planId',
            'conta'
        ));
    }

    public function create(): View
    {
        $servicos = Servico::query()
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        return view('admin.clientes.create', compact('servicos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:50'],
            'documento' => ['nullable', 'string', 'max:50'],

            'servico_id' => ['required', 'exists:servicos,id'],
            'valor' => ['nullable', 'numeric', 'min:0'],
            'tipo_cobranca' => ['required', Rule::in(['mensal', 'unico', 'anual'])],
            'status' => ['required', Rule::in(['ativo', 'pausado', 'concluido', 'cancelado'])],
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ]);

        $account = DB::transaction(function () use ($dados) {
            $account = Account::create([
                'nome' => $dados['nome'],
                'slug' => $this->gerarSlugUnico($dados['nome']),
                'documento' => $dados['documento'] ?? null,
                'telefone' => $dados['telefone'] ?? null,
                'email' => $dados['email'] ?? null,
                'ativo' => true,
            ]);

            $account->clienteServicos()->create([
                'servico_id' => $dados['servico_id'],
                'status' => $dados['status'],
                'valor' => $dados['valor'] ?? null,
                'tipo_cobranca' => $dados['tipo_cobranca'],
                'data_inicio' => $dados['data_inicio'] ?? now()->toDateString(),
                'data_fim' => $dados['data_fim'] ?? null,
                'observacoes' => $dados['observacoes'] ?? null,
            ]);

            return $account;
        });

        return redirect()
            ->route('admin.clientes.show', $account)
            ->with('success', 'Cliente cadastrado com sucesso.');
    }

    public function show(Account $account): View
    {
        $account->load([
            'users',
            'restaurantes',
            'subscription.plan',
        ]);

        $plans = Plan::where('ativo', true)
            ->orderBy('valor')
            ->get();

        $servicosDisponiveis = Servico::where('ativo', true)
            ->orderBy('nome')
            ->get();

        $clienteServicos = ClienteServico::query()
            ->with('servico')
            ->where('account_id', $account->id)
            ->latest()
            ->get();

        $subscription = $account->subscription;
        $plan = $subscription?->plan;
        $restaurante = $account->restaurantes->first();
        $user = $account->users->first();

        $diasRestantesTrial = null;

        if (
            $subscription?->status === 'trial'
            && $subscription->trial_ends_at
        ) {
            $diasRestantesTrial = max(
                0,
                now()->startOfDay()->diffInDays(
                    $subscription->trial_ends_at->copy()->startOfDay(),
                    false
                )
            );
        }

        return view('admin.clientes.show', compact(
            'account',
            'subscription',
            'plan',
            'restaurante',
            'user',
            'diasRestantesTrial',
            'plans',
            'servicosDisponiveis',
            'clienteServicos'
        ));
    }

    private function gerarSlugUnico(string $nome): string
    {
        $baseSlug = Str::slug($nome);
        $slug = $baseSlug;
        $contador = 1;

        while (Account::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $contador++;
        }

        return $slug;
    }
}