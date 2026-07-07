<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends BaseRestaurantController
{
    public function index()
    {
        $restaurante = $this->restaurante();

        $clientes = Cliente::where('restaurante_id', $restaurante->id)
            ->latest()
            ->get();

        return view('clientes.index', compact('clientes', 'restaurante'));
    }

    public function create()
    {
        $restaurante = $this->restaurante();

        return view('clientes.create', compact('restaurante'));
    }

    public function store(Request $request)
    {
        $restaurante = $this->restaurante();

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
                ->with('error', 'Já existe um cliente com este telefone neste restaurante.');
        }

        $dados['restaurante_id'] = $restaurante->id;

        Cliente::create($dados);

        return redirect()
            ->route('restaurante.clientes.index', $restaurante->slug)
            ->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function edit(Cliente $cliente)
    {
        $restaurante = $this->restaurante();

        $this->autorizarCliente($cliente);

        return view('clientes.edit', compact('cliente', 'restaurante'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $restaurante = $this->restaurante();

        $this->autorizarCliente($cliente);

        $dados = $request->validate([
            'nome' => 'required|string|max:255',
            'telefone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'observacao' => 'nullable|string',
        ]);

        $telefoneExiste = Cliente::where('restaurante_id', $restaurante->id)
            ->where('telefone', $dados['telefone'])
            ->where('id', '!=', $cliente->id)
            ->exists();

        if ($telefoneExiste) {
            return back()
                ->withInput()
                ->with('error', 'Já existe outro cliente com este telefone neste restaurante.');
        }

        $cliente->update($dados);

        return redirect()
            ->route('restaurante.clientes.index', $restaurante->slug)
            ->with('success', 'Cliente atualizado com sucesso!');
    }

    public function alterarStatus(Cliente $cliente)
    {
        $restaurante = $this->restaurante();

        $this->autorizarCliente($cliente);

        $cliente->update([
            'ativo' => ! $cliente->ativo,
        ]);

        return redirect()
            ->route('restaurante.clientes.index', $restaurante->slug)
            ->with('success', 'Status do cliente atualizado!');
    }

    private function autorizarCliente(Cliente $cliente): void
    {
        if ($cliente->restaurante_id !== $this->restaurante()->id) {
            abort(403);
        }
    }
}