<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\OnboardingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    public function create()
    {
        $plans = Plan::where('ativo', true)
            ->where('slug', '!=', 'enterprise')
            ->orderBy('valor')
            ->get();

        return view('saas.cadastro', compact('plans'));
    }

    public function store(Request $request, OnboardingService $onboarding)
    {
        $dados = $request->validate([
            'plan_slug' => 'required|exists:plans,slug',
            'user_nome' => 'required|string|max:255',
            'restaurante_nome' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'telefone' => 'nullable|string|max:50',
            'documento' => 'nullable|string|max:50',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $dados['account_nome'] = $dados['restaurante_nome'];
        $resultado = $onboarding->criarConta($dados);

        Auth::login($resultado['user']);

        return redirect()
            ->route('restaurante.dashboard', $resultado['restaurante']->slug)
            ->with('success', 'Bem-vindo ao Rima Food! Seu teste grátis foi iniciado.');
    }
}