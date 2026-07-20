<?php

namespace App\Http\Controllers;

use App\Models\Restaurante;
use App\Services\Delivery\Address;
use App\Services\Delivery\DeliveryCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

class DeliveryQuoteController extends Controller
{
    public function __invoke(
        Request $request,
        string $slug,
        DeliveryCalculator $calculator
    ): JsonResponse {
        $dados = $request->validate([
            'cep' => ['required', 'string', 'max:9'],
            'logradouro' => ['required', 'string', 'max:255'],
            'numero' => ['required', 'string', 'max:30'],
            'complemento' => ['nullable', 'string', 'max:100'],
            'bairro' => ['required', 'string', 'max:150'],
            'cidade' => ['required', 'string', 'max:150'],
            'estado' => ['required', 'string', 'size:2'],
        ]);

        $restaurante = Restaurante::query()
            ->where('slug', $slug)
            ->where('ativo', true)
            ->where('delivery', true)
            ->firstOrFail();

        $enderecoCliente = new Address(
            logradouro: $dados['logradouro'],
            numero: $dados['numero'],
            complemento: $dados['complemento'] ?? null,
            bairro: $dados['bairro'],
            cidade: $dados['cidade'],
            estado: strtoupper($dados['estado']),
            cep: $dados['cep'],
        );

        try {
            $resultado = $calculator->calcular(
                $restaurante,
                $enderecoCliente
            );

            return response()->json([
                'success' => true,
                'data' => $resultado->toArray(),
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível calcular a entrega.',
            ], 500);
        }
    }
}