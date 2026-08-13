<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\ClienteServico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminServicoController extends Controller
{
    public function store(Request $request, Account $account): RedirectResponse
    {
        $dados = $request->validate([
            'servico_id' => [
                'required',
                'exists:servicos,id',
                Rule::unique('cliente_servicos', 'servico_id')
                    ->where(fn ($query) => $query->where('account_id', $account->id)),
            ],
            'status' => ['required', Rule::in(['ativo', 'pausado', 'concluido', 'cancelado'])],
            'valor' => ['nullable', 'numeric', 'min:0'],
            'tipo_cobranca' => ['required', Rule::in(['mensal', 'unico', 'anual'])],
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ], [
            'servico_id.unique' => 'Este serviço já está vinculado a este cliente.',
        ]);

        $account->clienteServicos()->create($dados);

        return back()->with('success', 'Serviço adicionado ao cliente com sucesso.');
    }

    public function update(Request $request, Account $account, ClienteServico $clienteServico): RedirectResponse
    {
        $this->autorizarVinculo($account, $clienteServico);

        $dados = $request->validate([
            'status' => ['required', Rule::in(['ativo', 'pausado', 'concluido', 'cancelado'])],
            'valor' => ['nullable', 'numeric', 'min:0'],
            'tipo_cobranca' => ['required', Rule::in(['mensal', 'unico', 'anual'])],
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
        ]);

        $clienteServico->update($dados);

        return back()->with('success', 'Serviço atualizado com sucesso.');
    }

    public function destroy(Account $account, ClienteServico $clienteServico): RedirectResponse
    {
        $this->autorizarVinculo($account, $clienteServico);
        $clienteServico->delete();

        return back()->with('success', 'Serviço removido do cliente.');
    }

    private function autorizarVinculo(Account $account, ClienteServico $clienteServico): void
    {
        abort_unless($clienteServico->account_id === $account->id, 404);
    }
}