<?php

namespace App\Http\Controllers;

use App\Models\Restaurante;
use App\Services\GarcomCardapioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GarcomCardapioController extends Controller
{
    public function conversar(
        Request $request,
        string $slug,
        GarcomCardapioService $garcom
    ): JsonResponse {
        $dados = $request->validate([
            'pergunta' => [
                'required',
                'string',
                'min:2',
                'max:500',
            ],

            'historico' => [
                'nullable',
                'array',
                'max:8',
            ],

            'historico.*.autor' => [
                'required_with:historico',
                'in:cliente,garcom',
            ],

            'historico.*.mensagem' => [
                'required_with:historico',
                'string',
                'max:500',
            ],
        ]);

        Log::info('Garçom - Pergunta', [
            'restaurante' => $slug,
            'pergunta' => $dados['pergunta'],
        ]);

        $restaurante = Restaurante::with([
            'categorias' => fn($query) =>
                $query
                    ->where('ativo', true)
                    ->orderBy('nome'),

            'categorias.produtos' => fn($query) =>
                $query
                    ->where('ativo', true)
                    ->orderBy('nome'),
        ])
            ->where('slug', $slug)
            ->where('ativo', true)
            ->firstOrFail();

        if (!$restaurante->temIA()) {
            return response()->json([
                'mensagem' =>
                    'O Garçom Inteligente está disponível nos planos Rima Menu + IA e Rima Food.',
                'codigo' => 'upgrade_required',
            ], 403);
        }

        $resposta = $garcom->responder(
            $restaurante,
            $dados['pergunta'],
            $dados['historico'] ?? []
        );

        Log::info('Garçom - Resposta', [
            'mensagem' => $resposta['mensagem'],
            'produtos' => $resposta['produto_ids'],
            ]);

        return response()->json([
            'mensagem' => $resposta['mensagem'],
            'produto_ids' => $resposta['produto_ids'] ?? [],
        ]);
    }
}