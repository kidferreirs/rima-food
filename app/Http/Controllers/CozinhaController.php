<?php

namespace App\Http\Controllers;

use App\Models\Pedido;

class CozinhaController extends BaseRestaurantController
{
    public function index()
    {
        $restaurante = $this->restaurante();

        $novos = Pedido::with(['cliente', 'itens.produto'])
            ->where('restaurante_id', $restaurante->id)
            ->where('status', 'novo')
            ->latest()
            ->get();

        $preparo = Pedido::with(['cliente', 'itens.produto'])
            ->where('restaurante_id', $restaurante->id)
            ->where('status', 'preparando')
            ->latest()
            ->get();

        $prontos = Pedido::with(['cliente', 'itens.produto'])
            ->where('restaurante_id', $restaurante->id)
            ->where('status', 'pronto')
            ->latest()
            ->get();

        return view('cozinha.index', compact(
            'restaurante',
            'novos',
            'preparo',
            'prontos'
        ));
    }
}