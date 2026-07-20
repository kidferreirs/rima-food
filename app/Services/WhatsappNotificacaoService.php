<?php

namespace App\Services;

use App\Models\Pedido;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappNotificacaoService
{
    public function notificarNovoPedido(Pedido $pedido): bool
    {
        $webhookUrl = config('services.n8n.webhook_novo_pedido');

        if (empty($webhookUrl)) {
            Log::warning('Webhook do n8n para novo pedido não configurado.', [
                'pedido_id' => $pedido->id,
            ]);

            return false;
        }

        $pedido->loadMissing([
            'restaurante',
            'cliente',
            'itens.produto',
        ]);

        $restaurante = $pedido->restaurante;
        $cliente = $pedido->cliente;

        if (empty($restaurante?->telefone)) {
            Log::warning('Restaurante sem telefone para notificação.', [
                'pedido_id' => $pedido->id,
                'restaurante_id' => $pedido->restaurante_id,
            ]);

            return false;
        }

        $itens = $pedido->itens->map(function ($item) {
            return [
                'produto' => $item->produto?->nome ?? 'Produto',
                'quantidade' => $item->quantidade,
                'preco_unitario' => (float) $item->preco_unitario,
                'subtotal' => (float) ($item->preco_unitario * $item->quantidade),
            ];
        })->values()->all();

        $payload = [
            'evento' => 'novo_pedido',

            'restaurante' => [
                'id' => $restaurante->id,
                'nome' => $restaurante->nome,
                'telefone' => $this->normalizarTelefone($restaurante->telefone),
                'slug' => $restaurante->slug,
                'plano' => $restaurante->plano,
            ],

            'pedido' => [
                'id' => $pedido->id,
                'numero' => $pedido->numero_pedido,
                'token' => $pedido->token,
                'status' => $pedido->status,
                'origem' => $pedido->origem,
                'tipo_entrega' => $pedido->tipo_entrega,
                'forma_pagamento' => $pedido->forma_pagamento,
                'observacao' => $pedido->observacao,
                'subtotal' => (float) $pedido->subtotal,
                'total' => (float) $pedido->total,
                'criado_em' => $pedido->created_at?->toIso8601String(),
            ],

            'cliente' => [
                'id' => $cliente?->id,
                'nome' => $cliente?->nome,
                'telefone' => $this->normalizarTelefone($cliente?->telefone),
            ],

            'itens' => $itens,

            'mensagem' => $this->montarMensagem($pedido),
        ];

        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->asJson()
                ->post($webhookUrl, $payload);

            if ($response->failed()) {
                Log::error('Falha ao enviar notificação de pedido para o n8n.', [
                    'pedido_id' => $pedido->id,
                    'status_http' => $response->status(),
                    'resposta' => $response->body(),
                ]);

                return false;
            }

            Log::info('Notificação de novo pedido enviada ao n8n.', [
                'pedido_id' => $pedido->id,
                'numero_pedido' => $pedido->numero_pedido,
            ]);

            return true;
        } catch (\Throwable $exception) {
            Log::error('Erro ao comunicar com o webhook do n8n.', [
                'pedido_id' => $pedido->id,
                'erro' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function montarMensagem(Pedido $pedido): string
    {
        $tipoEntrega = match ($pedido->tipo_entrega) {
            'entrega' => 'Entrega',
            'retirada' => 'Retirada',
            'balcao' => 'Balcão',
            default => ucfirst((string) $pedido->tipo_entrega),
        };

        $formaPagamento = match ($pedido->forma_pagamento) {
            'pix' => 'Pix',
            'dinheiro' => 'Dinheiro',
            'credito' => 'Cartão de crédito',
            'debito' => 'Cartão de débito',
            default => ucfirst((string) $pedido->forma_pagamento),
        };

        $linhasItens = $pedido->itens
            ->map(function ($item) {
                $produto = $item->produto?->nome ?? 'Produto';

                return "• {$item->quantidade}x {$produto}";
            })
            ->implode("\n");

        $total = number_format((float) $pedido->total, 2, ',', '.');

        $mensagem = "🔔 *Novo pedido recebido!*\n\n";
        $mensagem .= "🧾 Pedido: #{$pedido->numero_pedido}\n";
        $mensagem .= "👤 Cliente: {$pedido->cliente?->nome}\n";
        $mensagem .= "📦 Atendimento: {$tipoEntrega}\n";
        $mensagem .= "💳 Pagamento: {$formaPagamento}\n\n";
        $mensagem .= "*Itens:*\n{$linhasItens}\n\n";
        $mensagem .= "💰 *Total: R$ {$total}*";

        if (!empty($pedido->observacao)) {
            $mensagem .= "\n\n📝 Observação: {$pedido->observacao}";
        }

        $mensagem .= "\n\nAcesse o painel do Rima Menu para visualizar o pedido.";

        return $mensagem;
    }

    private function normalizarTelefone(?string $telefone): ?string
    {
        if (empty($telefone)) {
            return null;
        }

        $numero = preg_replace('/\D/', '', $telefone);

        if (
            strlen($numero) === 10 ||
            strlen($numero) === 11
        ) {
            $numero = '55' . $numero;
        }

        return $numero;
    }
}