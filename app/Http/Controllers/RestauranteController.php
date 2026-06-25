<?php

namespace App\Http\Controllers;

use App\Models\Restaurante;
use Illuminate\Http\Request;

class RestauranteController extends Controller
{
    public function index()
    {
        $restaurantes = Restaurante::where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('restaurantes.index', compact('restaurantes'));
    }

    public function create()
    {
        return view('restaurantes.create');
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome' => 'required|string|max:255',
            'telefone' => 'nullable|string|max:50',
            'documento' => 'nullable|string|max:18',
            'email' => 'nullable|email|max:255',
            'cep' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:50',
        ]);

        $dados['user_id'] = auth()->id();

        Restaurante::create($dados);

        return redirect()
            ->route('restaurantes.index')
            ->with('success', 'Restaurante cadastrado com sucesso!');
    }

    public function edit(Restaurante $restaurante)
    {
        if ($restaurante->user_id !== auth()->id()) {
            abort(403);
        }

        return view('restaurantes.edit', compact('restaurante'));
    }

    public function update(Request $request, Restaurante $restaurante)
    {
        if ($restaurante->user_id !== auth()->id()) {
            abort(403);
        }

        $dados = $request->validate([
            'nome' => 'required|string|max:255',
            'telefone' => 'nullable|string|max:50',
            'documento' => 'nullable|string|max:18',
            'email' => 'nullable|email|max:255',
            'cep' => 'nullable|string|max:20',
            'endereco' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:50',
        ]);

        $restaurante->update($dados);

        return redirect()
            ->route('restaurantes.index')
            ->with('success', 'Restaurante atualizado com sucesso!');
    }

    public function alterarStatus(Restaurante $restaurante)
    {
        if ($restaurante->user_id !== auth()->id()) {
            abort(403);
        }

        $restaurante->update([
            'ativo' => ! $restaurante->ativo,
        ]);

        return redirect()
            ->route('restaurantes.index')
            ->with('success', 'Status do restaurante atualizado!');
    }
}