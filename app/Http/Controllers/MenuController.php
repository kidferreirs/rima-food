<?php

namespace App\Http\Controllers;
use App\Models\Produto;

use App\Models\Restaurante;

class MenuController extends Controller
{
    public function show(string $slug)
    {
        $restaurante = Restaurante::with([
            'categorias' => function ($query) {
                $query->where('ativo', true)
                    ->orderBy('nome');
            },

            'categorias.produtos' => function ($query) {
                $query->where('ativo', true)
                    ->orderBy('nome');
            },
        ])
            ->where('slug', $slug)
            ->where('ativo', true)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Bebidas sempre no final
        |--------------------------------------------------------------------------
        */

        $categoriasOrdenadas = $restaurante->categorias
            ->sortBy(function ($categoria) {
                $nome = mb_strtolower($categoria->nome);

                return str_contains($nome, 'bebida') ? 1 : 0;
            })
            ->values();

        $restaurante->setRelation('categorias', $categoriasOrdenadas);

        /*
        |--------------------------------------------------------------------------
        | Destaques virtuais
        |--------------------------------------------------------------------------
        |
        | O produto aparece nos destaques quando possuir uma destas tags:
        | destaque, mais vendido, recomendado, promoção ou novidade.
        |
        */

        $destaques = $restaurante->categorias
            ->flatMap(function ($categoria) {
                return $categoria->produtos->map(function ($produto) use ($categoria) {
                    $produto->setRelation('categoria', $categoria);

                    return $produto;
                });
            })
            ->filter(function ($produto) {
                $tags = mb_strtolower($produto->tags ?? '');

                return str_contains($tags, 'destaque')
                    || str_contains($tags, 'mais vendido')
                    || str_contains($tags, 'recomendado')
                    || str_contains($tags, 'promoção')
                    || str_contains($tags, 'promocao')
                    || str_contains($tags, 'novidade');
            })
            ->take(6)
            ->values();
        $destaquesIds = $destaques->pluck('id');

        /*
        |--------------------------------------------------------------------------
        | Mais vendidos reais
        |--------------------------------------------------------------------------
        */

        $maisVendidos = Produto::query()
            ->where('ativo', true)
            ->whereHas('categoria', function ($query) use ($restaurante) {
                $query
                    ->where('restaurante_id', $restaurante->id)
                    ->where('ativo', true);
            })
            ->withSum([
                'itensPedido as total_vendido' => function ($query) use ($restaurante) {
                    $query->whereHas('pedido', function ($pedidoQuery) use ($restaurante) {
                        $pedidoQuery
                            ->where('restaurante_id', $restaurante->id)
                            ->where('status', 'finalizado');
                    });
                },
            ], 'quantidade')
            ->orderByDesc('total_vendido')
            ->take(6)
            ->get()
            ->filter(fn($produto) => (int) $produto->total_vendido > 0)
            ->reject(fn($produto) => $destaquesIds->contains($produto->id))
            ->values();

        return view('menu.show', compact(
            'restaurante',
            'destaques',
            'maisVendidos'
        ));
    }
}