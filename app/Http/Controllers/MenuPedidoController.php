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
use App\Services\Delivery\Address;
use App\Services\Delivery\DeliveryCalculator;

class MenuPedidoController extends Controller
{
    public function checkout(string $slug)
    {
        $restaurante = Restaurante::where('slug', $slug)
            ->where('ativo', true)
            ->firstOrFail();

        return view('menu.checkout', compact('restaurante'));
    }

    public function store(Request $request, string $slug, WhatsappNotificacaoService $whatsappNotificacao, DeliveryCalculator $deliveryCalculator)
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
            'cep' => 'required_if:tipo_entrega,entrega',
            'logradouro' => 'required_if:tipo_entrega,entrega',
            'numero' => 'required_if:tipo_entrega,entrega',
            'complemento' => 'nullable|string',
            'bairro' => 'required_if:tipo_entrega,entrega',
            'cidade' => 'required_if:tipo_entrega,entrega',
            'estado' => 'required_if:tipo_entrega,entrega',
        ]);

        $carrinho = json_decode($dados['carrinho'], true);

        if (!$carrinho || count($carrinho) === 0) {
            return back()->with('error', 'Seu carrinho está vazio.');
        }

        $pedido = DB::transaction(function () use ($dados, $carrinho, $restaurante, $deliveryCalculator) {
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

            $subtotalPedido = $total;
            $taxaEntrega = 0;

            if ($dados['tipo_entrega'] === 'entrega') {

                $address = new Address(
                    logradouro: $dados['logradouro'],
                    numero: $dados['numero'],
                    complemento: $dados['complemento'] ?? null,
                    bairro: $dados['bairro'],
                    cidade: $dados['cidade'],
                    estado: strtoupper($dados['estado']),
                    cep: $dados['cep'],
                );

                $resultadoEntrega = $deliveryCalculator->calcular(
                    $restaurante,
                    $address
                );

                $taxaEntrega = $resultadoEntrega->taxa;
            }

            $totalPedido = $subtotalPedido + $taxaEntrega;

            $pedido->update([
                'subtotal' => $subtotalPedido,
                'taxa_entrega' => $taxaEntrega,
                'total' => $totalPedido,
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