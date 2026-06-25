<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\Restaurante;

class DashboardController extends Controller
{
    public function index()
    {
        $restaurante = Restaurante::where('user_id', auth()->id())
            ->first();

        $produtos = 0;
        $clientes = 0;
        $totalPedidos = 0;
        $pedidosPendentes = 0;
        $ultimosPedidos = collect();
        $dinheiroHoje = 0;
        $cartaoHoje = 0;
        $pixHoje = 0;

        if ($restaurante) {
            $produtos = Produto::whereHas('categoria', function ($query) use ($restaurante) {
                $query->where('restaurante_id', $restaurante->id);
            })->count();

            $clientes = Cliente::where('restaurante_id', $restaurante->id)
                ->count();

            $totalPedidos = Pedido::where('restaurante_id', $restaurante->id)
                ->count();

            $pedidosPendentes = Pedido::where('restaurante_id', $restaurante->id)
                ->whereIn('status', [
                    'novo',
                    'preparando',
                    'pronto',
                    'saiu_entrega',
                ])
                ->count();

            $ultimosPedidos = Pedido::with([
                'cliente',
                'itens.produto',
            ])
                ->where('restaurante_id', $restaurante->id)
                ->latest()
                ->limit(5)
                ->get();

            $dinheiroHoje = Pedido::where('restaurante_id', $restaurante->id)

                ->whereDate('created_at', today())

                ->where('forma_pagamento', 'dinheiro')

                ->sum('total');


            $cartaoHoje = Pedido::where('restaurante_id', $restaurante->id)

                ->whereDate('created_at', today())

                ->where('forma_pagamento', 'cartao')

                ->sum('total');


            $pixHoje = Pedido::where('restaurante_id', $restaurante->id)

                ->whereDate('created_at', today())

                ->where('forma_pagamento', 'pix')

                ->sum('total');
        }

        return view('dashboard.index', compact(
            'restaurante',
            'produtos',
            'clientes',
            'totalPedidos',
            'pedidosPendentes',
            'ultimosPedidos',
            'dinheiroHoje',
            'cartaoHoje',
            'pixHoje'
        ));
    }
}