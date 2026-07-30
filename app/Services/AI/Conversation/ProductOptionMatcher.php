<?php

namespace App\Services\AI\Conversation;

use App\Services\AI\Knowledge\ProductOptionKnowledge;
use Illuminate\Support\Str;

class ProductOptionMatcher
{
    public function __construct(
        private readonly ProductOptionKnowledge $productOptionKnowledge
    ) {
    }

    /**
     * Encontra todas as opções válidas mencionadas na mensagem.
     */
    public function encontrarOpcoes(
        int $produtoId,
        string $mensagem
    ): array {
        $mensagemNormalizada = $this->normalizar($mensagem);

        if ($mensagemNormalizada === '') {
            return [];
        }

        $grupos = $this->productOptionKnowledge
            ->gruposDoProduto($produtoId);

        $opcoesEncontradas = [];

        foreach ($grupos as $grupo) {
            foreach ($grupo['opcoes'] as $opcao) {
                $nomeNormalizado = $this->normalizar(
                    (string) $opcao['nome']
                );

                if (
                    $nomeNormalizado === ''
                    || !$this->mensagemContemOpcao(
                        $mensagemNormalizada,
                        $nomeNormalizado
                    )
                ) {
                    continue;
                }

                $opcoesEncontradas[$opcao['id']] = $this->normalizarOpcao(
                    $grupo,
                    $opcao
                );
            }
        }

        return array_values($opcoesEncontradas);
    }

    /**
     * Encontra somente opções pertencentes a um grupo.
     */
    public function encontrarOpcoesDoGrupo(
        array $grupo,
        string $mensagem
    ): array {
        $mensagemNormalizada = $this->normalizar($mensagem);

        if ($mensagemNormalizada === '') {
            return [];
        }

        $opcoesEncontradas = [];

        foreach ($grupo['opcoes'] as $opcao) {
            $nomeNormalizado = $this->normalizar(
                (string) $opcao['nome']
            );

            if (
                $nomeNormalizado === ''
                || !$this->mensagemContemOpcao(
                    $mensagemNormalizada,
                    $nomeNormalizado
                )
            ) {
                continue;
            }

            $opcoesEncontradas[$opcao['id']] = $this->normalizarOpcao(
                $grupo,
                $opcao
            );
        }

        return array_values($opcoesEncontradas);
    }

    public function encontrouAlgumaOpcao(
        int $produtoId,
        string $mensagem
    ): bool {
        return $this->encontrarOpcoes(
            $produtoId,
            $mensagem
        ) !== [];
    }

    /**
     * Retorna os grupos obrigatórios do produto.
     */
    public function gruposObrigatorios(int $produtoId): array
    {
        return array_values(
            array_filter(
                $this->productOptionKnowledge
                    ->gruposDoProduto($produtoId),
                fn (array $grupo): bool =>
                    (int) ($grupo['minimo'] ?? 0) > 0
            )
        );
    }

    public function primeiroGrupoObrigatorio(
        int $produtoId
    ): ?array {
        $grupos = $this->gruposObrigatorios($produtoId);

        return $grupos[0] ?? null;
    }

    public function buscarGrupo(
        int $produtoId,
        int $grupoId
    ): ?array {
        foreach (
            $this->productOptionKnowledge
                ->gruposDoProduto($produtoId)
            as $grupo
        ) {
            if ((int) $grupo['id'] === $grupoId) {
                return $grupo;
            }
        }

        return null;
    }

    /**
     * Valida uma resposta para um grupo específico.
     */
    public function validarRespostaGrupo(
        array $grupo,
        string $mensagem,
        OrderItem $item
    ): array {
        $opcoes = $this->encontrarOpcoesDoGrupo(
            $grupo,
            $mensagem
        );

        $minimo = max(
            0,
            (int) ($grupo['minimo'] ?? 0)
        );

        $maximo = max(
            $minimo,
            (int) ($grupo['maximo'] ?? 0)
        );

        if (empty($opcoes)) {
            return [
                'valido' => false,
                'motivo' => 'opcao_nao_encontrada',
                'mensagem' => $this->mensagemOpcaoInvalida($grupo),
                'opcoes' => [],
            ];
        }

        if (count($opcoes) > $maximo) {
            return [
                'valido' => false,
                'motivo' => 'maximo_excedido',
                'mensagem' =>
                    "Você pode escolher no máximo {$maximo} "
                    . $this->pluralizarOpcao($maximo)
                    . " para {$grupo['nome']}.",
                'opcoes' => [],
            ];
        }

        $quantidadeAtual = $item->quantidadeOpcoesGrupo(
            (int) $grupo['id']
        );

        $quantidadeNova = $quantidadeAtual + count($opcoes);

        if ($quantidadeNova > $maximo) {
            return [
                'valido' => false,
                'motivo' => 'maximo_excedido',
                'mensagem' =>
                    "Você pode escolher no máximo {$maximo} "
                    . $this->pluralizarOpcao($maximo)
                    . " para {$grupo['nome']}.",
                'opcoes' => [],
            ];
        }

        return [
            'valido' => true,
            'motivo' => null,
            'mensagem' => null,
            'opcoes' => $opcoes,
        ];
    }

    public function grupoEstaCompleto(
        array $grupo,
        OrderItem $item
    ): bool {
        $quantidade = $item->quantidadeOpcoesGrupo(
            (int) $grupo['id']
        );

        $minimo = max(
            0,
            (int) ($grupo['minimo'] ?? 0)
        );

        $maximo = max(
            $minimo,
            (int) ($grupo['maximo'] ?? 0)
        );

        return $quantidade >= $minimo
            && $quantidade <= $maximo;
    }

    public function montarPerguntaGrupo(array $grupo): string
    {
        $minimo = max(
            0,
            (int) ($grupo['minimo'] ?? 0)
        );

        $maximo = max(
            $minimo,
            (int) ($grupo['maximo'] ?? 0)
        );

        if ($minimo === 1 && $maximo === 1) {
            $texto = "Escolha uma opção para {$grupo['nome']}:";
        } elseif ($minimo === $maximo) {
            $texto = "Escolha {$minimo} "
                . $this->pluralizarOpcao($minimo)
                . " para {$grupo['nome']}:";
        } else {
            $texto = "Escolha entre {$minimo} e {$maximo} opções "
                . "para {$grupo['nome']}:";
        }

        $linhas = [$texto];

        foreach ($grupo['opcoes'] as $opcao) {
            $linha = "• {$opcao['nome']}";

            if ((float) ($opcao['valor'] ?? 0) > 0) {
                $linha .= ' (+R$ '
                    . number_format(
                        (float) $opcao['valor'],
                        2,
                        ',',
                        '.'
                    )
                    . ')';
            }

            $linhas[] = $linha;
        }

        return implode(PHP_EOL, $linhas);
    }

    public function mensagemOpcaoInvalida(array $grupo): string
    {
        return 'Não reconheci uma opção válida para '
            . "{$grupo['nome']}."
            . PHP_EOL
            . $this->montarPerguntaGrupo($grupo);
    }

    private function normalizarOpcao(
        array $grupo,
        array $opcao
    ): array {
        return [
            'id' => (int) $opcao['id'],
            'grupo_id' => (int) $grupo['id'],
            'grupo' => (string) $grupo['nome'],
            'nome' => (string) $opcao['nome'],
            'valor' => round(
                (float) ($opcao['valor'] ?? 0),
                2
            ),
        ];
    }

    private function mensagemContemOpcao(
        string $mensagem,
        string $opcao
    ): bool {
        $padrao = '/(?:^|\s)'
            . preg_quote($opcao, '/')
            . '(?:$|\s)/u';

        return preg_match($padrao, $mensagem) === 1;
    }

    private function pluralizarOpcao(int $quantidade): string
    {
        return $quantidade === 1
            ? 'opção'
            : 'opções';
    }

    private function normalizar(string $texto): string
    {
        return Str::of($texto)
            ->lower()
            ->ascii()
            ->trim()
            ->replaceMatches('/[^\pL\pN\s\-]/u', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->toString();
    }
}