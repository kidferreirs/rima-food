<?php

namespace App\Http\Controllers;

use App\Models\Restaurante;

class MenuController extends Controller
{
    public function show(string $slug)
    {
        $restaurante = Restaurante::with([
            'categorias' => function ($query) {
                $query->where('ativo', true)->orderBy('nome');
            },
            'categorias.produtos' => function ($query) {
                $query->where('ativo', true)->orderBy('nome');
            },
        ])
            ->where('slug', $slug)
            ->where('ativo', true)
            ->firstOrFail();

        return view('menu.show', compact('restaurante'));
    }
}