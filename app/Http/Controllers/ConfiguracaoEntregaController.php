<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracaoEntrega;
use App\Models\Restaurante;
use Illuminate\Http\Request;

class ConfiguracaoEntregaController extends Controller
{
    public function index()
    {
        $restaurante = Restaurante::where(
            'user_id',
            auth()->id()
        )->first();

        if (!$restaurante) {
            return redirect()
                ->route('restaurantes.create');
        }

        $configuracao = ConfiguracaoEntrega::firstOrCreate(
            [
                'restaurante_id' => $restaurante->id,
            ],
            [
                'ate_5km' => 5,
                'ate_10km' => 8,
                'acima_10km' => 12,
            ]
        );

        return view(
            'configuracoes.entregas',
            compact(
                'configuracao'
            )
        );
    }

    public function salvar(Request $request)
    {
        $dados = $request->validate([
            'ate_5km' => 'required|numeric|min:0',
            'ate_10km' => 'required|numeric|min:0',
            'acima_10km' => 'required|numeric|min:0',
        ]);

        $restaurante = Restaurante::where(
            'user_id',
            auth()->id()
        )->first();

        $configuracao = ConfiguracaoEntrega::firstOrCreate([
            'restaurante_id' => $restaurante->id,
        ]);

        $configuracao->update($dados);

        return back()->with(
            'success',
            'Configurações salvas!'
        );
    }
}