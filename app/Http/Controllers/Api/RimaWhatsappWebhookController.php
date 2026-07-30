<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConversaWhatsapp;
use App\Models\Restaurante;
use App\Services\RimaEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RimaWhatsappWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        RimaEngine $rima
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Validar chave enviada pelo n8n
        |--------------------------------------------------------------------------
        */

        $chaveRecebida = (string) $request->header(
            'X-Rima-Webhook-Secret'
        );

        $chaveConfigurada = (string) config(
            'services.rima_whatsapp.webhook_secret'
        );

        if (
            $chaveConfigurada === ''
            || !hash_equals(
                $chaveConfigurada,
                $chaveRecebida
            )
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Não autorizado.',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Validar payload normalizado pelo n8n
        |--------------------------------------------------------------------------
        */

        $dados = $request->validate([
            'restaurante_id' => [
                'required',
                'integer',
                'exists:restaurantes,id',
            ],

            'message_id' => [
                'required',
                'string',
                'max:255',
            ],

            'telefone' => [
                'required',
                'string',
                'max:30',
            ],

            'nome_cliente' => [
                'nullable',
                'string',
                'max:255',
            ],

            'mensagem' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Impedir mensagem duplicada
        |--------------------------------------------------------------------------
        */

        $chaveDuplicidade =
            'rima-whatsapp-message:'
            . $dados['message_id'];

        $primeiroProcessamento = Cache::add(
            $chaveDuplicidade,
            true,
            now()->addHours(24)
        );

        if (!$primeiroProcessamento) {
            return response()->json([
                'success' => true,
                'duplicate' => true,
                'message' => 'Mensagem já processada.',
            ]);
        }

        try {
            $restaurante = Restaurante::findOrFail(
                $dados['restaurante_id']
            );

            $telefone = preg_replace(
                '/\D+/',
                '',
                $dados['telefone']
            );

            if ($telefone === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Telefone inválido.',
                ], 422);
            }

            $conversa = ConversaWhatsapp::firstOrCreate(
                [
                    'restaurante_id' => $restaurante->id,
                    'telefone' => $telefone,
                ],
                [
                    'nome_cliente' =>
                        $dados['nome_cliente']
                        ?? 'Cliente WhatsApp',

                    'estado' => 'inicio',
                    'carrinho' => [],
                    'contexto_ia' => null,
                    'pedido_confirmado' => false,
                    'atendimento_humano' => false,
                    'historico' => [],
                ]
            );

            if (
                !empty($dados['nome_cliente'])
                && (
                    empty($conversa->nome_cliente)
                    || $conversa->nome_cliente
                    === 'Cliente WhatsApp'
                )
            ) {
                $conversa->nome_cliente =
                    $dados['nome_cliente'];

                $conversa->save();
            }

            /*
             * Se um atendente humano assumiu a conversa,
             * a IA não deve responder.
             */
            if ($conversa->atendimento_humano) {
                return response()->json([
                    'success' => true,
                    'human_service' => true,
                    'reply' => null,
                    'conversa_id' => $conversa->id,
                ]);
            }

            $resposta = $rima->processar(
                $conversa,
                $dados['mensagem']
            );

            return response()->json([
                'success' => true,
                'duplicate' => false,
                'conversa_id' => $conversa->id,
                'telefone' => $telefone,
                'reply' => $resposta,
            ]);
        } catch (\Throwable $exception) {
            Cache::forget($chaveDuplicidade);

            Log::error(
                'Erro ao processar WhatsApp da Rima.',
                [
                    'message_id' => $dados['message_id'],
                    'restaurante_id' =>
                        $dados['restaurante_id'],

                    'telefone' => $dados['telefone'],
                    'erro' => $exception->getMessage(),
                    'arquivo' => $exception->getFile(),
                    'linha' => $exception->getLine(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' =>
                    'Não foi possível processar a mensagem.',
            ], 500);
        }
    }
}