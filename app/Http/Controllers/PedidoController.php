<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\Restaurante;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::with([
            'cliente',
            'itens.produto',
        ])
            ->whereHas('restaurante', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->orderByRaw("
                CASE
                    WHEN prioritario = 1
                    AND status NOT IN ('finalizado', 'cancelado')
                    THEN 0
                    ELSE 1
                END
            ")
            ->orderByRaw("
                CASE status
                    WHEN 'novo' THEN 1
                    WHEN 'preparando' THEN 2
                    WHEN 'pronto' THEN 3
                    WHEN 'saiu_entrega' THEN 4
                    WHEN 'finalizado' THEN 5
                    WHEN 'cancelado' THEN 6
                    ELSE 7
                END
            ")
            ->latest()
            ->get();

        return view('pedidos.index', compact('pedidos'));
    }

    public function create()
    {
        $restaurante = Restaurante::where('user_id', auth()->id())
            ->where('ativo', true)
            ->first();

        if (!$restaurante) {
            return redirect()
                ->route('restaurantes.create')
                ->with('success', 'Cadastre um restaurante primeiro.');
        }

        Cliente::firstOrCreate(
            [
                'restaurante_id' => $restaurante->id,
                'telefone' => '00000000000',
            ],
            [
                'nome' => 'Balcão',
                'email' => null,
                'observacao' => 'Cliente padrão para pedidos presenciais.',
                'ativo' => true,
            ]
        );

        $clientes = Cliente::where('restaurante_id', $restaurante->id)
            ->where('ativo', true)
            ->orderByRaw("CASE WHEN telefone = '00000000000' THEN 0 ELSE 1 END")
            ->orderBy('nome')
            ->get();

        $produtos = Produto::with('categoria')
            ->whereHas('categoria', function ($query) use ($restaurante) {
                $query->where('restaurante_id', $restaurante->id);
            })
            ->where('ativo', true)
            ->get();

        return view('pedidos.create', compact(
            'restaurante',
            'clientes',
            'produtos'
        ));
    }

    public function store(Request $request)
    {
        $restaurante = Restaurante::where('user_id', auth()->id())
            ->where('ativo', true)
            ->first();

        if (!$restaurante) {
            abort(403);
        }

        $dados = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'produto_id' => 'required|array|min:1',
            'produto_id.*' => 'required|exists:produtos,id',
            'quantidade' => 'required|array|min:1',
            'quantidade.*' => 'required|integer|min:1',
            'observacao' => 'nullable|string',
            'forma_pagamento' => 'required|in:dinheiro,cartao,pix',
            'prioritario' => 'nullable|boolean',
            'tipo_entrega' => 'required|in:balcao,retirada,entrega',
            'taxa_entrega' => 'nullable|numeric|min:0',
            'endereco_entrega' => 'nullable|string',
        ]);

        $clienteOk = Cliente::where('id', $dados['cliente_id'])
            ->where('restaurante_id', $restaurante->id)
            ->exists();

        if (!$clienteOk) {
            abort(403);
        }

        [$itens, $totalProdutos] = $this->montarItens($dados, $restaurante);

        $taxaEntrega = $this->calcularTaxaEntrega($dados);
        $total = $totalProdutos + $taxaEntrega;

        $pedido = Pedido::create([
            'restaurante_id' => $restaurante->id,
            'cliente_id' => $dados['cliente_id'],
            'total' => $total,
            'status' => 'novo',
            'origem' => 'balcao',
            'observacao' => $dados['observacao'] ?? null,
            'forma_pagamento' => $dados['forma_pagamento'],
            'prioritario' => $request->boolean('prioritario'),
            'novo_em' => now(),
            'tipo_entrega' => $dados['tipo_entrega'],
            'taxa_entrega' => $taxaEntrega,
            'endereco_entrega' => $dados['tipo_entrega'] === 'entrega'
                ? ($dados['endereco_entrega'] ?? null)
                : null,
        ]);

        foreach ($itens as $item) {
            $pedido->itens()->create($item);
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Pedido criado com sucesso!');
    }

    public function show(Pedido $pedido)
    {
        $pedido->load([
            'cliente',
            'itens.produto',
            'restaurante',
        ]);

        if ($pedido->restaurante->user_id !== auth()->id()) {
            abort(403);
        }

        return view('pedidos.show', compact('pedido'));
    }

    public function edit(Pedido $pedido)
    {
        if ($pedido->restaurante->user_id !== auth()->id()) {
            abort(403);
        }

        $restaurante = $pedido->restaurante;

        $clientes = Cliente::where('restaurante_id', $restaurante->id)
            ->where('ativo', true)
            ->orderByRaw("CASE WHEN telefone = '00000000000' THEN 0 ELSE 1 END")
            ->orderBy('nome')
            ->get();

        $produtos = Produto::with('categoria')
            ->whereHas('categoria', function ($query) use ($restaurante) {
                $query->where('restaurante_id', $restaurante->id);
            })
            ->where('ativo', true)
            ->get();

        $pedido->load('itens.produto');

        return view('pedidos.edit', compact(
            'pedido',
            'restaurante',
            'clientes',
            'produtos'
        ));
    }

    public function update(Request $request, Pedido $pedido)
    {
        $pedido->load('restaurante');

        if ($pedido->restaurante->user_id !== auth()->id()) {
            abort(403);
        }

        if (in_array($pedido->status, ['cancelado', 'finalizado'])) {
            return redirect()
                ->route('pedidos.index')
                ->with('success', 'Pedido finalizado ou cancelado não pode ser editado.');
        }

        $restaurante = $pedido->restaurante;

        $dados = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'produto_id' => 'required|array|min:1',
            'produto_id.*' => 'required|exists:produtos,id',
            'quantidade' => 'required|array|min:1',
            'quantidade.*' => 'required|integer|min:1',
            'observacao' => 'nullable|string',
            'forma_pagamento' => 'required|in:dinheiro,cartao,pix',
            'prioritario' => 'nullable|boolean',
            'tipo_entrega' => 'required|in:balcao,retirada,entrega',
            'taxa_entrega' => 'nullable|numeric|min:0',
            'endereco_entrega' => 'nullable|string',
        ]);

        $clienteOk = Cliente::where('id', $dados['cliente_id'])
            ->where('restaurante_id', $restaurante->id)
            ->exists();

        if (!$clienteOk) {
            abort(403);
        }

        [$itens, $totalProdutos] = $this->montarItens($dados, $restaurante);

        $taxaEntrega = $this->calcularTaxaEntrega($dados);
        $total = $totalProdutos + $taxaEntrega;

        $pedido->update([
            'cliente_id' => $dados['cliente_id'],
            'total' => $total,
            'observacao' => $dados['observacao'] ?? null,
            'forma_pagamento' => $dados['forma_pagamento'],
            'prioritario' => $request->boolean('prioritario'),
            'tipo_entrega' => $dados['tipo_entrega'],
            'taxa_entrega' => $taxaEntrega,
            'endereco_entrega' => $dados['tipo_entrega'] === 'entrega'
                ? ($dados['endereco_entrega'] ?? null)
                : null,
        ]);

        $pedido->itens()->delete();

        foreach ($itens as $item) {
            $pedido->itens()->create($item);
        }

        return redirect()
            ->route('pedidos.index')
            ->with('success', 'Pedido atualizado com sucesso!');
    }

    public function alterarStatus(Request $request, Pedido $pedido)
    {
        $pedido->load('restaurante');

        if ($pedido->restaurante->user_id !== auth()->id()) {
            abort(403);
        }

        if (in_array($pedido->status, ['cancelado', 'finalizado'])) {
            return back()
                ->with('success', 'Este pedido já foi finalizado ou cancelado e não pode ser alterado.');
        }

        $request->validate([
            'status' => 'required|in:novo,preparando,pronto,saiu_entrega,finalizado,cancelado',
        ]);

        $dadosAtualizacao = [
            'status' => $request->status,
        ];

        if ($request->status === 'preparando' && !$pedido->preparando_em) {
            $dadosAtualizacao['preparando_em'] = now();
        }

        if ($request->status === 'pronto' && !$pedido->pronto_em) {
            $dadosAtualizacao['pronto_em'] = now();
        }

        if ($request->status === 'finalizado' && !$pedido->finalizado_em) {
            $dadosAtualizacao['finalizado_em'] = now();
        }

        $pedido->update($dadosAtualizacao);

        return back()
            ->with('success', 'Status atualizado!');
    }

    public function cancelar(Pedido $pedido)
    {
        $pedido->load('restaurante');

        if ($pedido->restaurante->user_id !== auth()->id()) {
            abort(403);
        }

        if ($pedido->status === 'finalizado') {
            return back()
                ->with('success', 'Pedido finalizado não pode ser cancelado.');
        }

        if ($pedido->status === 'cancelado') {
            return back()
                ->with('success', 'Este pedido já está cancelado.');
        }

        $pedido->update([
            'status' => 'cancelado',
        ]);

        return back()
            ->with('success', 'Pedido cancelado com sucesso!');
    }

    public function imprimir(Pedido $pedido)
    {
        if ($pedido->restaurante->user_id !== auth()->id()) {
            abort(403);
        }

        $pedido->load([
            'cliente',
            'itens.produto',
            'restaurante',
        ]);

        return view('pedidos.imprimir', compact('pedido'));
    }

    private function montarItens(array $dados, Restaurante $restaurante): array
    {
        $total = 0;
        $itens = [];

        foreach ($dados['produto_id'] as $index => $produtoId) {
            $quantidade = $dados['quantidade'][$index] ?? 1;

            $produto = Produto::where('id', $produtoId)
                ->whereHas('categoria', function ($query) use ($restaurante) {
                    $query->where('restaurante_id', $restaurante->id);
                })
                ->firstOrFail();

            $subtotal = $produto->preco * $quantidade;
            $total += $subtotal;

            $itens[] = [
                'produto_id' => $produto->id,
                'quantidade' => $quantidade,
                'preco_unitario' => $produto->preco,
            ];
        }

        return [$itens, $total];
    }

    private function calcularTaxaEntrega(array $dados): float
    {
        if (($dados['tipo_entrega'] ?? 'balcao') !== 'entrega') {
            return 0;
        }

        return (float) ($dados['taxa_entrega'] ?? 0);
    }
}