<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ItemPedido;
use App\Models\Pedido;

class RelatorioController extends BaseRestaurantController
{
    public function index()
    {
        $restaurante = $this->restaurante();

        $faturamentoHoje = 0;
        $faturamentoMes = 0;
        $faturamentoSemanal = 0;
        $faturamentoPeriodo = 0;

        $ticketMedio = 0;
        $produtoMaisVendido = null;
        $novosClientes = 0;
        $pedidosFinalizados = 0;
        $pedidosCancelados = 0;

        $dinheiroPeriodo = 0;
        $creditoPeriodo = 0;
        $debitoPeriodo = 0;
        $cartaoPeriodo = 0;
        $pixPeriodo = 0;
        $formaMaisUsada = 'Nenhuma';

        $erroPeriodo = null;

        $dataInicio = request('data_inicio');
        $dataFim = request('data_fim');
        $atalho = request('atalho');

        if ($atalho === 'hoje') {
            $dataInicio = today()->format('Y-m-d');
            $dataFim = today()->format('Y-m-d');
        }

        if ($atalho === 'ontem') {
            $dataInicio = now()->subDay()->format('Y-m-d');
            $dataFim = now()->subDay()->format('Y-m-d');
        }

        if ($atalho === 'semana') {
            $dataInicio = now()->startOfWeek()->format('Y-m-d');
            $dataFim = now()->endOfWeek()->format('Y-m-d');
        }

        if ($atalho === 'mes') {
            $dataInicio = now()->startOfMonth()->format('Y-m-d');
            $dataFim = now()->endOfMonth()->format('Y-m-d');
        }

        if ($dataInicio && $dataFim && $dataFim < $dataInicio) {
            $erroPeriodo = 'A data final não pode ser menor que a data inicial.';
            $dataFim = null;
        }

        $temFiltro = request()->filled('atalho')
            || (
                request()->filled('data_inicio')
                && request()->filled('data_fim')
            );

        if (!$erroPeriodo) {
            $queryFinalizados = Pedido::query()
                ->where('restaurante_id', $restaurante->id)
                ->where('status', 'finalizado');

            $faturamentoHoje = (clone $queryFinalizados)
                ->whereDate('created_at', today())
                ->sum('total');

            $faturamentoSemanal = (clone $queryFinalizados)
                ->whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ])
                ->sum('total');

            $faturamentoMes = (clone $queryFinalizados)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total');

            $queryPeriodo = Pedido::query()
                ->where('restaurante_id', $restaurante->id);

            if ($dataInicio && $dataFim) {
                $queryPeriodo->whereBetween('created_at', [
                    $dataInicio,
                    $dataFim . ' 23:59:59',
                ]);
            }

            $queryFinalizadosPeriodo = (clone $queryPeriodo)
                ->where('status', 'finalizado');

            $faturamentoPeriodo = (clone $queryFinalizadosPeriodo)
                ->sum('total');

            $pedidosFinalizados = (clone $queryPeriodo)
                ->where('status', 'finalizado')
                ->count();

            $pedidosCancelados = (clone $queryPeriodo)
                ->where('status', 'cancelado')
                ->count();

            $ticketMedio = $pedidosFinalizados > 0
                ? $faturamentoPeriodo / $pedidosFinalizados
                : 0;

            $novosClientes = Cliente::query()
                ->where('restaurante_id', $restaurante->id)
                ->where('telefone', '!=', '00000000000')
                ->when(
                    $dataInicio && $dataFim,
                    fn($query) => $query->whereBetween('created_at', [
                        $dataInicio,
                        $dataFim . ' 23:59:59',
                    ])
                )
                ->count();

            $produtoMaisVendido = ItemPedido::query()
                ->with('produto')
                ->whereHas('pedido', function ($query) use (
                    $restaurante,
                    $dataInicio,
                    $dataFim
                ) {
                    $query
                        ->where('restaurante_id', $restaurante->id)
                        ->where('status', 'finalizado')
                        ->when(
                            $dataInicio && $dataFim,
                            fn($query) => $query->whereBetween('created_at', [
                                $dataInicio,
                                $dataFim . ' 23:59:59',
                            ])
                        );
                })
                ->selectRaw(
                    'produto_id, SUM(quantidade) as total_vendido'
                )
                ->groupBy('produto_id')
                ->orderByDesc('total_vendido')
                ->first();

            $queryFinanceiro = Pedido::query()
                ->where('restaurante_id', $restaurante->id)
                ->where('status', 'finalizado')
                ->when(
                    $dataInicio && $dataFim,
                    fn($query) => $query->whereBetween('created_at', [
                        $dataInicio,
                        $dataFim . ' 23:59:59',
                    ])
                );

            $dinheiroPeriodo = (clone $queryFinanceiro)
                ->where('forma_pagamento', 'dinheiro')
                ->sum('total');

            $creditoPeriodo = (clone $queryFinanceiro)
                ->where('forma_pagamento', 'credito')
                ->sum('total');

            $debitoPeriodo = (clone $queryFinanceiro)
                ->where('forma_pagamento', 'debito')
                ->sum('total');

            $cartaoGenericoPeriodo = (clone $queryFinanceiro)
                ->where('forma_pagamento', 'cartao')
                ->sum('total');

            $cartaoPeriodo =
                $creditoPeriodo
                + $debitoPeriodo
                + $cartaoGenericoPeriodo;

            $pixPeriodo = (clone $queryFinanceiro)
                ->where('forma_pagamento', 'pix')
                ->sum('total');

            $formas = [
                'Dinheiro' => (float) $dinheiroPeriodo,
                'Cartão' => (float) $cartaoPeriodo,
                'Pix' => (float) $pixPeriodo,
            ];

            $maiorValor = max($formas);

            if ($maiorValor > 0) {
                $formaMaisUsada = array_search(
                    $maiorValor,
                    $formas,
                    true
                );
            }
        }

        return view('relatorios.index', compact(
            'restaurante',
            'faturamentoHoje',
            'faturamentoSemanal',
            'faturamentoMes',
            'faturamentoPeriodo',
            'ticketMedio',
            'produtoMaisVendido',
            'novosClientes',
            'pedidosFinalizados',
            'pedidosCancelados',
            'dataInicio',
            'dataFim',
            'atalho',
            'erroPeriodo',
            'dinheiroPeriodo',
            'cartaoPeriodo',
            'creditoPeriodo',
            'debitoPeriodo',
            'pixPeriodo',
            'formaMaisUsada',
            'temFiltro',
        ));
    }

    public function exportar()
    {
        $restaurante = $this->restaurante();

        $dataInicio = request('data_inicio');
        $dataFim = request('data_fim');
        $atalho = request('atalho');

        if ($atalho === 'hoje') {
            $dataInicio = today()->format('Y-m-d');
            $dataFim = today()->format('Y-m-d');
        }

        if ($atalho === 'ontem') {
            $dataInicio = now()->subDay()->format('Y-m-d');
            $dataFim = now()->subDay()->format('Y-m-d');
        }

        if ($atalho === 'semana') {
            $dataInicio = now()->startOfWeek()->format('Y-m-d');
            $dataFim = now()->endOfWeek()->format('Y-m-d');
        }

        if ($atalho === 'mes') {
            $dataInicio = now()->startOfMonth()->format('Y-m-d');
            $dataFim = now()->endOfMonth()->format('Y-m-d');
        }

        $pedidos = Pedido::with(['cliente', 'itens.produto'])
            ->where('restaurante_id', $restaurante->id)
            ->when(
                $dataInicio && $dataFim,
                fn($query) => $query->whereBetween('created_at', [
                    $dataInicio,
                    $dataFim . ' 23:59:59',
                ])
            )
            ->latest()
            ->get();

        $nomeArquivo =
            'relatorio-pedidos-'
            . now()->format('Y-m-d-H-i')
            . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' =>
                "attachment; filename=\"{$nomeArquivo}\"",
        ];

        return response()->stream(function () use ($pedidos) {
            $arquivo = fopen('php://output', 'w');

            fprintf(
                $arquivo,
                chr(0xEF) . chr(0xBB) . chr(0xBF)
            );

            fputcsv($arquivo, [
                'Pedido',
                'Cliente',
                'Itens',
                'Total',
                'Status',
                'Origem',
                'Data',
            ], ';');

            foreach ($pedidos as $pedido) {
                $itens = $pedido->itens
                    ->map(
                        fn($item) =>
                            $item->quantidade
                            . 'x '
                            . $item->produto->nome
                    )
                    ->implode(' | ');

                fputcsv($arquivo, [
                    '#'
                    . ($pedido->numero_pedido ?? $pedido->id),
                    $pedido->cliente->nome ?? '',
                    $itens,
                    number_format(
                        $pedido->total,
                        2,
                        ',',
                        '.'
                    ),
                    $pedido->status,
                    $pedido->origem,
                    $pedido->created_at->format('d/m/Y H:i'),
                ], ';');
            }

            fclose($arquivo);
        }, 200, $headers);
    }
}