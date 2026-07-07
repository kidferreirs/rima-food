<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConfiguracaoEntregaController extends BaseRestaurantController
{
    public function index()
    {
        $restaurante = $this->restaurante();

        $configuracao = $restaurante->configuracaoEntrega()->firstOrCreate([
            'restaurante_id' => $restaurante->id,
        ], [
            'ate_5km' => 0,
            'ate_10km' => 0,
            'acima_10km' => 0,
        ]);

        return view('configuracoes.entregas', compact(
            'restaurante',
            'configuracao'
        ));
    }

    public function salvar(Request $request)
    {
        $restaurante = $this->restaurante();

        $dados = $request->validate([
            'ate_5km' => 'nullable|numeric|min:0',
            'ate_10km' => 'nullable|numeric|min:0',
            'acima_10km' => 'nullable|numeric|min:0',
        ]);

        $restaurante->configuracaoEntrega()->updateOrCreate(
            [
                'restaurante_id' => $restaurante->id,
            ],
            [
                'ate_5km' => $dados['ate_5km'] ?? 0,
                'ate_10km' => $dados['ate_10km'] ?? 0,
                'acima_10km' => $dados['acima_10km'] ?? 0,
            ]
        );

        return redirect()
            ->route('restaurante.configuracoes.entregas.index', $restaurante->slug)
            ->with('success', 'Configurações de entrega salvas com sucesso!');
    }
}