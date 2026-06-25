<?php

namespace App\Http\Controllers;

use App\Models\Pedido;

class CozinhaController extends Controller
{
    public function index()
    {
        $novos = Pedido::with(['cliente', 'itens.produto'])
            ->where('status', 'novo')
            ->latest()
            ->get();

        $preparo = Pedido::with(['cliente', 'itens.produto'])
            ->where('status', 'preparando')
            ->latest()
            ->get();

        $prontos = Pedido::with(['cliente', 'itens.produto'])
            ->where('status', 'pronto')
            ->latest()
            ->get();

        return view('cozinha.index', compact('novos', 'preparo', 'prontos'));
    }
}