<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Restaurante;

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

        if ($restaurante && !$erroPeriodo) {
            $queryBase = Pedido::where('restaurante_id', $restaurante->id)
                ->where('status', 'finalizado');

            $faturamentoHoje = (clone $queryBase)
                ->whereDate('created_at', today())
                ->sum('total');

            $faturamentoSemanal = (clone $queryBase)
                ->whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ])
                ->sum('total');

            $faturamentoMes = (clone $queryBase)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total');

            if ($dataInicio && $dataFim) {
                $faturamentoPeriodo = (clone $queryBase)
                    ->whereBetween('created_at', [
                        $dataInicio,
                        $dataFim . ' 23:59:59',
                    ])
                    ->sum('total');

                if ($faturamentoPeriodo == 0) {
                    $faturamentoHoje = 0;
                    $faturamentoSemanal = 0;
                    $faturamentoMes = 0;
                }
            }

            $pedidosValidos = Pedido::where('restaurante_id', $restaurante->id)
                ->where('status', 'finalizado')
                ->count();

            $faturamentoValido = Pedido::where('restaurante_id', $restaurante->id)
                ->where('status', 'finalizado')
                ->sum('total');

            $ticketMedio = $pedidosValidos > 0
                ? $faturamentoValido / $pedidosValidos
                : 0;

            $novosClientes = Cliente::where('restaurante_id', $restaurante->id)
                ->where('telefone', '!=', '00000000000')
                ->count();

            $pedidosFinalizados = Pedido::where('restaurante_id', $restaurante->id)
                ->where('status', 'finalizado')
                ->count();

            $pedidosCancelados = Pedido::where('restaurante_id', $restaurante->id)
                ->where('status', 'cancelado')
                ->count();

            $produtoMaisVendido = ItemPedido::with('produto')
                ->whereHas('pedido', function ($query) use ($restaurante) {
                    $query->where('restaurante_id', $restaurante->id)
                        ->where('status', 'finalizado');
                })
                ->selectRaw('produto_id, SUM(quantidade) as total_vendido')
                ->groupBy('produto_id')
                ->orderByDesc('total_vendido')
                ->first();

            $queryFinanceiro = Pedido::where('restaurante_id', $restaurante->id)
                ->where('status', 'finalizado');

            if ($dataInicio && $dataFim) {
                $queryFinanceiro->whereBetween('created_at', [
                    $dataInicio,
                    $dataFim . ' 23:59:59',
                ]);
            }

            $dinheiroPeriodo = (clone $queryFinanceiro)
                ->where('forma_pagamento', 'dinheiro')
                ->sum('total');

            $cartaoPeriodo = (clone $queryFinanceiro)
                ->where('forma_pagamento', 'cartao')
                ->sum('total');

            $pixPeriodo = (clone $queryFinanceiro)
                ->where('forma_pagamento', 'pix')
                ->sum('total');

            $formas = [
                '💵 Dinheiro' => $dinheiroPeriodo,
                '💳 Cartão' => $cartaoPeriodo,
                '🏦 Pix' => $pixPeriodo,
            ];

            $maiorValor = max($formas);

            if ($maiorValor > 0) {
                $formaMaisUsada = array_search($maiorValor, $formas);
            }
        }

        $temFiltro = false;
        if (request()->filled('atalho')) {
            $temFiltro = true;
        }

        if (request()->filled('data_inicio') && request()->filled('data_fim')) {
            $temFiltro = true;
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
            ->when($dataInicio && $dataFim, function ($query) use ($dataInicio, $dataFim) {
                $query->whereBetween('created_at', [
                    $dataInicio,
                    $dataFim . ' 23:59:59',
                ]);
            })
            ->latest()
            ->get();

        $nomeArquivo = 'relatorio-pedidos-' . now()->format('Y-m-d-H-i') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$nomeArquivo\"",
        ];

        return response()->stream(function () use ($pedidos) {
            $arquivo = fopen('php://output', 'w');

            fprintf($arquivo, chr(0xEF) . chr(0xBB) . chr(0xBF));

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
                $itens = $pedido->itens->map(function ($item) {
                    return $item->quantidade . 'x ' . $item->produto->nome;
                })->implode(' | ');

                fputcsv($arquivo, [
                    '#' . $pedido->id,
                    $pedido->cliente->nome ?? '',
                    $itens,
                    number_format($pedido->total, 2, ',', '.'),
                    $pedido->status,
                    $pedido->origem,
                    $pedido->created_at->format('d/m/Y H:i'),
                ], ';');
            }

            fclose($arquivo);
        }, 200, $headers);
    }
}