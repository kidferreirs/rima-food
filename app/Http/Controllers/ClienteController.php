<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Restaurante;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::with('restaurante')
            ->whereHas('restaurante', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->latest()
            ->get();

        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        $restaurante = Restaurante::where('user_id', auth()->id())
            ->where('ativo', true)
            ->first();

        return view('clientes.create', compact('restaurante'));
    }

    public function store(Request $request)
    {
        $restaurante = Restaurante::where('user_id', auth()->id())
            ->where('ativo', true)
            ->first();

        if (! $restaurante) {
            return redirect()
                ->route('restaurantes.create')
                ->with('success', 'Cadastre um restaurante primeiro.');
        }

        $dados = $request->validate([
            'nome' => 'required|string|max:255',
            'telefone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'observacao' => 'nullable|string',
        ]);

        $telefoneExiste = Cliente::where('restaurante_id', $restaurante->id)
            ->where('telefone', $dados['telefone'])
            ->exists();

        if ($telefoneExiste) {
            return back()
                ->withInput()
                ->with('error', 'Já existe um cliente com este telefone.');
        }

        $dados['restaurante_id'] = $restaurante->id;

        Cliente::create($dados);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function edit(Cliente $cliente)
    {
        $this->validarCliente($cliente);

        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $this->validarCliente($cliente);

        $dados = $request->validate([
            'nome' => 'required|string|max:255',
            'telefone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'observacao' => 'nullable|string',
        ]);

        $telefoneExiste = Cliente::where('restaurante_id', $cliente->restaurante_id)
            ->where('telefone', $dados['telefone'])
            ->where('id', '!=', $cliente->id)
            ->exists();

        if ($telefoneExiste) {
            return back()
                ->withInput()
                ->with('error', 'Já existe outro cliente com este telefone.');
        }

        $cliente->update($dados);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Cliente atualizado com sucesso!');
    }

    public function alterarStatus(Cliente $cliente)
    {
        $this->validarCliente($cliente);

        $cliente->update([
            'ativo' => ! $cliente->ativo,
        ]);

        return redirect()
            ->route('clientes.index')
            ->with('success', 'Status do cliente atualizado!');
    }

    private function validarCliente(Cliente $cliente)
    {
        $ok = Cliente::where('id', $cliente->id)
            ->whereHas('restaurante', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->exists();

        if (! $ok) {
            abort(403);
        }
    }
}