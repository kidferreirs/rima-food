<?php

namespace App\Services\AI\Conversation;

use Illuminate\Support\Str;

class ConversationInterpreter
{
    public const REPETIR_PRODUTO = 'repetir_produto';
    public const ADICIONAR_PRODUTO_QUANTIDADE = 'adicionar_produto_quantidade';
    public const ADICIONAR_OBSERVACAO = 'adicionar_observacao';
    public const CONSULTAR_PRODUTO = 'consultar_produto';
    public const CONSULTAR_CATEGORIA = 'consultar_categoria';
    public const REMOVER_ULTIMO_ITEM = 'remover_ultimo_item';
    public const DESCONHECIDA = 'desconhecida';
    public function interpretar(
        string $mensagem,
        ConversationContext $contexto
    ): array {
        $texto = $this->normalizar($mensagem);

        /*
        |--------------------------------------------------------------------------
        | Remover último produto
        |--------------------------------------------------------------------------
        */

        if ($this->solicitouRemocaoItem($texto)) {
            return [
                'intent' => self::REMOVER_ULTIMO_ITEM,
                'quantidade' => 1,
                'termo_produto' => null,
                'observacao' => null,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Observações do produto
        |--------------------------------------------------------------------------
        |
        | Exemplos:
        | sem cebola
        | tira o tomate
        | com bacon
        | adiciona cheddar
        |
        */

        $observacao = $this->extrairObservacao($texto);

        if ($observacao !== null && !empty($contexto->itens)) {
            return [
                'intent' => self::ADICIONAR_OBSERVACAO,
                'quantidade' => 1,
                'termo_produto' => null,
                'observacao' => $observacao,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Repetição contextual
        |--------------------------------------------------------------------------
        |
        | Exemplos:
        | mais um
        | mais dois
        | o mesmo
        | outro igual
        |
        */

        $quantidadeRepeticao = $this->quantidadeRepeticao($texto);

        if (
            $quantidadeRepeticao !== null
            && $contexto->produto !== null
        ) {
            return [
                'intent' => self::REPETIR_PRODUTO,
                'quantidade' => $quantidadeRepeticao,
                'termo_produto' => null,
                'observacao' => null,
            ];
        }

        /*
 |--------------------------------------------------------------------------
 | Produto com quantidade
 |--------------------------------------------------------------------------
 */

        $produtoComQuantidade = $this->extrairProdutoComQuantidade($texto);

        if ($produtoComQuantidade !== null) {
            return [
                'intent' => self::ADICIONAR_PRODUTO_QUANTIDADE,
                'quantidade' => $produtoComQuantidade['quantidade'],
                'termo_produto' => $produtoComQuantidade['produto'],
                'observacao' => null,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Consulta de produtos e categorias
        |--------------------------------------------------------------------------
        */

        $consulta = $this->extrairConsulta($texto);

        if ($consulta !== null) {
            return $consulta;
        }

        /*
        |--------------------------------------------------------------------------
        | Desconhecida
        |--------------------------------------------------------------------------
        */

        return [
            'intent' => self::DESCONHECIDA,
            'quantidade' => 1,
            'termo_produto' => null,
            'observacao' => null,
        ];
    }

    private function quantidadeRepeticao(string $texto): ?int
    {
        if (
            in_array($texto, [
                'o mesmo',
                'mais um',
                'mais uma',
                'outro igual',
                'outra igual',
                'repete',
                'repetir',
            ], true)
        ) {
            return 1;
        }

        if (
            preg_match(
                '/^(?:mais|quero mais)\s+(\d+|um|uma|dois|duas|tres|quatro|cinco)$/',
                $texto,
                $resultado
            )
        ) {
            return $this->converterQuantidade($resultado[1]);
        }

        return null;
    }

    private function extrairProdutoComQuantidade(string $texto): ?array
    {
        $texto = preg_replace(
            '/^(?:sim|sim,|claro|ok|beleza)\s+/u',
            '',
            $texto
        );

        $texto = preg_replace(
            '/^(eu\s+)?(quero|gostaria de|me ve|manda|coloca|adiciona|adicione|adicionar)\s+/u',
            '',
            $texto
        );

        $padrao = '/^(?:(?:mais)\s+)?(\d+|um|uma|dois|duas|tres|quatro|cinco)\s+(.+)$/';

        if (!preg_match($padrao, $texto, $resultado)) {
            return null;
        }

        $quantidade = $this->converterQuantidade($resultado[1]);
        $produto = $this->normalizarNomeProduto(
            trim($resultado[2])
        );

        if ($quantidade < 1 || $produto === '') {
            return null;
        }

        return [
            'quantidade' => $quantidade,
            'produto' => $produto,
        ];
    }

    private function extrairObservacao(string $texto): ?string
    {
        $padroes = [
            '/^sem\s+(.+)$/' => 'Sem $1',
            '/^tira(?:r)?\s+(?:o|a|os|as)?\s*(.+)$/' => 'Sem $1',
            '/^remove(?:r)?\s+(?:o|a|os|as)?\s*(.+)$/' => 'Sem $1',
            '/^com\s+(.+)$/' => 'Com $1',
            '/^adiciona(?:r)?\s+(.+)$/' => 'Adicionar $1',
            '/^coloca(?:r)?\s+(.+)$/' => 'Adicionar $1',
        ];

        foreach ($padroes as $padrao => $formato) {
            if (preg_match($padrao, $texto, $resultado)) {
                return str_replace(
                    '$1',
                    trim($resultado[1]),
                    $formato
                );
            }
        }

        return null;
    }

    private function solicitouRemocaoItem(string $texto): bool
    {
        return in_array($texto, [
            'remove o ultimo',
            'remove o ultimo item',
            'tira o ultimo',
            'tira o ultimo item',
            'cancela o ultimo',
            'nao quero esse',
            'remove esse',
        ], true);
    }

    private function extrairConsulta(string $texto): ?array
    {
        if (
            preg_match(
                '/^(?:tem|possui|vende|quero|mostra|mostrar|cardapio|cardápio|quais)\s+(.+)$/u',
                $texto,
                $resultado
            )
        ) {
            $termo = trim($resultado[1]);

            return [
                'intent' => self::CONSULTAR_PRODUTO,
                'quantidade' => 1,
                'termo_produto' => $this->normalizarNomeProduto($termo),
                'observacao' => null,
            ];
        }

        return null;
    }

    private function converterQuantidade(string $quantidade): int
    {
        return match ($quantidade) {
            'um', 'uma' => 1,
            'dois', 'duas' => 2,
            'tres' => 3,
            'quatro' => 4,
            'cinco' => 5,
            default => max(1, (int) $quantidade),
        };
    }

    private function normalizar(string $mensagem): string
    {
        return Str::of($mensagem)
            ->lower()
            ->ascii()
            ->trim()
            ->replaceMatches('/[^\pL\pN\s\-]/u', '')
            ->replaceMatches('/\s+/', ' ')
            ->toString();
    }
    private function normalizarNomeProduto(
        string $produto
    ): string {
        $produto = trim($produto);

        /*
         * Remove somente artigos genéricos.
         * Não conhece nenhum segmento ou tipo de alimento.
         */
        $produto = preg_replace(
            '/^(?:o|a|os|as|um|uma|uns|umas)\s+/u',
            '',
            $produto
        ) ?? $produto;

        /*
         * Remove expressões genéricas no final.
         */
        $produto = preg_replace(
            '/\s+(?:tem|disponivel|disponiveis)$/u',
            '',
            $produto
        ) ?? $produto;

        return Str::of($produto)
            ->lower()
            ->ascii()
            ->trim()
            ->replaceMatches('/[^\pL\pN\s\-]/u', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->toString();
    }
}