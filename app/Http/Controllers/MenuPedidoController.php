<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\Restaurante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\WhatsappNotificacaoService;

class MenuPedidoController extends Controller
{
    public function checkout(string $slug)
    {
        $restaurante = Restaurante::where('slug', $slug)
            ->where('ativo', true)
            ->firstOrFail();

        return view('menu.checkout', compact('restaurante'));
    }

    public function store( Request $request, string $slug,WhatsappNotificacaoService $whatsappNotificacao)
    {

        $restaurante = Restaurante::where('slug', $slug)
            ->where('ativo', true)
            ->firstOrFail();

        $dados = $request->validate([
            'nome' => 'required|string|max:255',
            'telefone' => 'required|string|max:50',
            'tipo_entrega' => 'required|in:retirada,balcao,entrega',
            'forma_pagamento' => 'required|in:pix,dinheiro,credito,debito',
            'observacao' => 'nullable|string',
            'carrinho' => 'required|string',
        ]);

        $carrinho = json_decode($dados['carrinho'], true);

        if (!$carrinho || count($carrinho) === 0) {
            return back()->with('error', 'Seu carrinho está vazio.');
        }

        $pedido = DB::transaction(function () use ($dados, $carrinho, $restaurante) {
            $cliente = Cliente::firstOrCreate(
                [
                    'restaurante_id' => $restaurante->id,
                    'telefone' => $dados['telefone'],
                ],
                [
                    'nome' => $dados['nome'],
                    'ativo' => true,
                ]
            );

            $numeroPedido = Pedido::proximoNumero($restaurante->id);

            $pedido = Pedido::create([
                'restaurante_id' => $restaurante->id,
                'cliente_id' => $cliente->id,
                'status' => 'novo',
                'origem' => 'menu',
                'tipo_entrega' => $dados['tipo_entrega'],
                'forma_pagamento' => $dados['forma_pagamento'],
                'observacao' => $dados['observacao'] ?? null,
                'total' => 0,
                'novo_em' => now(),
                'token' => Str::upper(Str::random(10)),
                'numero_pedido' => $numeroPedido,
            ]);

            $total = 0;

            foreach ($carrinho as $item) {
                $produto = Produto::where('id', $item['id'])
                    ->whereHas('categoria', function ($query) use ($restaurante) {
                        $query->where('restaurante_id', $restaurante->id);
                    })
                    ->firstOrFail();

                $quantidade = (int) $item['quantidade'];
                $subtotal = $produto->preco * $quantidade;
                $total += $subtotal;

                $pedido->itens()->create([
                    'produto_id' => $produto->id,
                    'quantidade' => $quantidade,
                    'preco_unitario' => $produto->preco,
                ]);
            }

            $pedido->update([
                'subtotal' => $total,
                'total' => $total,
            ]);

            return $pedido;
        });

        $whatsappNotificacao->notificarNovoPedido($pedido);

        return redirect()->route('pedido.sucesso', $pedido->token);
    }

    public function sucesso(string $token)
    {
        $pedido = Pedido::with(['restaurante', 'cliente', 'itens.produto'])
            ->where('token', $token)
            ->firstOrFail();

        return view('menu.sucesso', compact('pedido'));
    }
}