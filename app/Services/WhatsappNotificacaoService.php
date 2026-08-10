<?php

namespace App\Services;

use App\Models\Pedido;
use Illuminate\Support\Facades\Log;

class WhatsappNotificacaoService
{
    public function __construct(
        private readonly EvolutionApiService $evolution
    ) {
    }
    public function notificarNovoPedido(Pedido $pedido): bool
    {
        $pedido->loadMissing([
            'restaurante',
            'cliente',
            'itens.produto',
        ]);

        $restaurante = $pedido->restaurante;

        if (empty($restaurante?->telefone)) {
            Log::warning('Restaurante sem telefone para notificação.', [
                'pedido_id' => $pedido->id,
                'restaurante_id' => $pedido->restaurante_id,
            ]);

            return false;
        }

        $instancia = (string) config(
            'services.evolution.notification_instance'
        );

        if ($instancia === '') {
            Log::warning('Instância central de notificações não configurada.', [
                'pedido_id' => $pedido->id,
            ]);

            return false;
        }

        $numero = $this->normalizarTelefone(
            $restaurante->telefone
        );

        $mensagem = $this->montarMensagem($pedido);

        try {
            $this->evolution->enviarTexto(
                $instancia,
                $numero,
                $mensagem
            );

            Log::info('Notificação de novo pedido enviada por WhatsApp.', [
                'pedido_id' => $pedido->id,
                'numero_pedido' => $pedido->numero_pedido,
                'restaurante_id' => $restaurante->id,
                'telefone' => $numero,
            ]);

            return true;
        } catch (\Throwable $exception) {
            Log::error('Erro ao enviar notificação de pedido por WhatsApp.', [
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