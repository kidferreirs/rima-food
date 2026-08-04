<?php

namespace App\Services\AI\Importacao;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class CardapioEnrichmentService
{
    private string $apiKey;

    private string $model;

    private string $baseUrl;

    private int $timeout;

    public function __construct()
    {
        $this->apiKey = trim((string) config('services.ai.key'));
        $this->model = trim((string) config('services.ai.model'));
        $this->baseUrl = rtrim(
            trim((string) config(
                'services.ai.base_url',
                'https://api.openai.com/v1'
            )),
            '/'
        );
        $this->timeout = max(
            30,
            (int) config('services.ai.timeout', 90)
        );
    }

    /**
     * Enriquece as linhas já estruturadas sem alterar categoria,
     * nome ou preço.
     *
     * @param array<int, array<string, mixed>> $linhas
     * @return array<int, array<string, mixed>>
     */
    public function enriquecer(array $linhas): array
    {
        $this->validarConfiguracao();

        if ($linhas === []) {
            throw new RuntimeException(
                'Não há produtos para melhorar com IA.'
            );
        }

        $produtos = [];

        foreach (array_values($linhas) as $indice => $linha) {
            $produtos[] = [
                'indice' => $indice,
                'categoria' => trim(
                    (string) ($linha['categoria'] ?? '')
                ),
                'nome' => trim(
                    (string) ($linha['nome'] ?? '')
                ),
                'descricao_atual' => trim(
                    (string) ($linha['descricao'] ?? '')
                ),
                'preco' => (float) ($linha['preco'] ?? 0),
            ];
        }

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->withToken($this->apiKey)
                ->timeout($this->timeout)
                ->retry(
                    2,
                    1200,
                    fn(Throwable $exception) =>
                        $exception instanceof ConnectionException
                )
                ->post(
                    $this->baseUrl . '/responses',
                    $this->montarPayload($produtos)
                );

            $response->throw();

            $conteudo = $this->extrairTextoResposta(
                $response->json()
            );

            $resposta = json_decode(
                $conteudo,
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            return $this->aplicarEnriquecimento(
                $linhas,
                (array) ($resposta['produtos'] ?? [])
            );
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'Não foi possível conectar ao serviço de IA.',
                previous: $exception
            );
        } catch (RequestException $exception) {
            $mensagemApi = data_get(
                $exception->response?->json(),
                'error.message'
            );

            throw new RuntimeException(
                $mensagemApi
                    ? 'A IA recusou a solicitação: ' . $mensagemApi
                    : 'O serviço de IA retornou um erro ao melhorar o cardápio.',
                previous: $exception
            );
        } catch (RuntimeException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'Não foi possível melhorar o cardápio com IA.',
                previous: $exception
            );
        }
    }

    private function validarConfiguracao(): void
    {
        if ($this->apiKey === '') {
            throw new RuntimeException(
                'A variável AI_API_KEY não foi configurada.'
            );
        }

        if ($this->model === '') {
            throw new RuntimeException(
                'A variável AI_MODEL não foi configurada.'
            );
        }
    }

    private function montarPayload(array $produtos): array
    {
        return [
            'model' => $this->model,
            'input' => [
                [
                    'role' => 'system',
                    'content' => $this->promptSistema(),
                ],
                [
                    'role' => 'user',
                    'content' =>
                        "Melhore os produtos abaixo.\n\n"
                        . json_encode(
                            ['produtos' => $produtos],
                            JSON_UNESCAPED_UNICODE
                            | JSON_UNESCAPED_SLASHES
                            | JSON_PRETTY_PRINT
                        ),
                ],
            ],
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => 'cardapio_enriquecido',
                    'strict' => true,
                    'schema' => $this->schema(),
                ],
            ],
            'temperature' => 0,
            'max_output_tokens' => 16000,
        ];
    }

    private function promptSistema(): string
    {
        return <<<'PROMPT'
Você melhora dados de produtos de cardápios brasileiros.

REGRAS:

1. Não altere o índice recebido.
2. Não altere nome, categoria ou preço.
3. Não invente ingredientes que não estejam no nome ou na descrição atual.
4. A descrição deve ser curta, objetiva e comercial, sem exageros.
5. Não use palavras como "irresistível", "incrível", "o melhor" ou promessas não comprovadas.
6. Se já existir uma boa descrição, preserve o significado e apenas melhore a escrita.
7. Gere palavras-chave úteis para busca, em português, sem duplicações.
8. Gere sinônimos e formas comuns de o cliente pedir o produto.
9. Liste somente ingredientes que possam ser identificados pelo nome ou descrição.
10. Restrições devem conter somente informações explícitas ou diretamente seguras:
   - lactose quando houver queijo, leite, creme ou requeijão;
   - glúten quando houver pão, massa, bolo ou empanado;
   - não invente outras alergias.
11. Tags permitidas:
   - vegetariano
   - vegano
   - sem_gluten
   - sem_lactose
   - apimentado
12. Só use uma tag quando houver evidência clara. Caso contrário, retorne lista vazia.
13. Não marque produtos como destaque.
14. Retorne todos os produtos recebidos.
15. Retorne somente o JSON exigido pelo schema.
PROMPT;
    }

    private function schema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'produtos' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'indice' => [
                                'type' => 'integer',
                                'minimum' => 0,
                            ],
                            'descricao' => [
                                'type' => 'string',
                            ],
                            'palavras_chave' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'string',
                                ],
                            ],
                            'sinonimos' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'string',
                                ],
                            ],
                            'ingredientes' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'string',
                                ],
                            ],
                            'restricoes' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'string',
                                ],
                            ],
                            'tags' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'string',
                                    'enum' => [
                                        'vegetariano',
                                        'vegano',
                                        'sem_gluten',
                                        'sem_lactose',
                                        'apimentado',
                                    ],
                                ],
                            ],
                        ],
                        'required' => [
                            'indice',
                            'descricao',
                            'palavras_chave',
                            'sinonimos',
                            'ingredientes',
                            'restricoes',
                            'tags',
                        ],
                    ],
                ],
            ],
            'required' => [
                'produtos',
            ],
        ];
    }

    private function extrairTextoResposta(array $resposta): string
    {
        $outputText = data_get($resposta, 'output_text');

        if (is_string($outputText) && trim($outputText) !== '') {
            return trim($outputText);
        }

        foreach ((array) data_get($resposta, 'output', []) as $item) {
            foreach ((array) data_get($item, 'content', []) as $conteudo) {
                if (data_get($conteudo, 'type') === 'refusal') {
                    throw new RuntimeException(
                        'A IA recusou o enriquecimento deste cardápio.'
                    );
                }

                if (data_get($conteudo, 'type') === 'output_text') {
                    $texto = data_get($conteudo, 'text');

                    if (is_string($texto) && trim($texto) !== '') {
                        return trim($texto);
                    }
                }
            }
        }

        throw new RuntimeException(
            'A IA não retornou os dados enriquecidos.'
        );
    }

    private function aplicarEnriquecimento(
        array $linhas,
        array $enriquecidos
    ): array {
        foreach ($enriquecidos as $produto) {
            $indice = $produto['indice'] ?? null;

            if (
                !is_int($indice)
                || !array_key_exists($indice, $linhas)
            ) {
                continue;
            }

            $linhas[$indice]['descricao'] = $this->limparTexto(
                $produto['descricao'] ?? $linhas[$indice]['descricao'] ?? ''
            );

            foreach ([
                'palavras_chave',
                'sinonimos',
                'ingredientes',
                'restricoes',
                'tags',
            ] as $campo) {
                $linhas[$indice][$campo] = $this->normalizarLista(
                    $produto[$campo] ?? []
                );
            }
        }

        return array_values($linhas);
    }

    private function normalizarLista(mixed $itens): array
    {
        if (!is_array($itens)) {
            return [];
        }

        $resultado = [];

        foreach ($itens as $item) {
            $texto = $this->limparTexto($item);

            if ($texto === '') {
                continue;
            }

            $chave = mb_strtolower($texto);

            if (!isset($resultado[$chave])) {
                $resultado[$chave] = $texto;
            }
        }

        return array_values($resultado);
    }

    private function limparTexto(mixed $texto): string
    {
        $texto = trim((string) $texto);

        return preg_replace('/\s+/u', ' ', $texto) ?? $texto;
    }
}