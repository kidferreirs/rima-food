<?php

namespace App\Services\AI\Importacao;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class CardapioAnalysisService
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
     * Gera sugestões de grupos e uma auditoria geral.
     *
     * @param array<int, array<string, mixed>> $linhas
     * @return array{
     *     produtos: array<int, array<string, mixed>>,
     *     auditoria: array<string, mixed>
     * }
     */
    public function analisar(array $linhas): array
    {
        $this->validarConfiguracao();

        if ($linhas === []) {
            throw new RuntimeException(
                'Não há produtos para analisar.'
            );
        }

        $produtos = [];

        foreach (array_values($linhas) as $indice => $linha) {
            $produtos[] = [
                'indice' => $indice,
                'categoria' => (string) (
                    $linha['categoria'] ?? ''
                ),
                'nome' => (string) (
                    $linha['nome'] ?? ''
                ),
                'descricao' => (string) (
                    $linha['descricao'] ?? ''
                ),
                'preco' => (float) (
                    $linha['preco'] ?? 0
                ),
                'ingredientes' => array_values(
                    (array) ($linha['ingredientes'] ?? [])
                ),
                'palavras_chave' => array_values(
                    (array) ($linha['palavras_chave'] ?? [])
                ),
                'sinonimos' => array_values(
                    (array) ($linha['sinonimos'] ?? [])
                ),
                'restricoes' => array_values(
                    (array) ($linha['restricoes'] ?? [])
                ),
                'tags' => array_values(
                    (array) ($linha['tags'] ?? [])
                ),
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

            $dados = json_decode(
                $conteudo,
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            return $this->normalizarResultado(
                $linhas,
                $dados
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
                    ? 'A IA recusou a análise: ' . $mensagemApi
                    : 'O serviço de IA retornou um erro na análise.',
                previous: $exception
            );
        } catch (RuntimeException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'Não foi possível analisar o cardápio.',
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
                        "Analise este cardápio.\n\n"
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
                    'name' => 'analise_cardapio',
                    'strict' => true,
                    'schema' => $this->schema(),
                ],
            ],
            'temperature' => 0,
            'max_output_tokens' => 18000,
        ];
    }

    private function promptSistema(): string
    {
        return <<<'PROMPT'
Você analisa cardápios brasileiros já estruturados.

OBJETIVOS:
1. Sugerir grupos de configuração por produto.
2. Auditar a qualidade e a cobertura do cardápio.

REGRAS PARA GRUPOS:
1. As sugestões não serão aplicadas automaticamente.
2. Retorne no máximo 3 grupos por produto.
3. Tipos permitidos: CHECKBOX, RADIO e TEXT.
4. Valores das opções devem ser sempre 0, pois o restaurante definirá preços depois.
5. Sugira "Remover ingredientes" quando existirem ingredientes removíveis.
6. Sugira "Ponto da carne" somente para hambúrguer, carne bovina ou filé.
7. Sugira "Adicionais" apenas quando houver ingredientes que façam sentido como extras.
8. Não sugira "Tipo de pão" se o tipo de pão não estiver no cardápio.
9. Não invente sabores, tamanhos ou ingredientes.
10. Cada grupo deve ter uma justificativa curta.
11. CHECKBOX: mínimo 0; máximo entre 1 e 5.
12. RADIO: mínimo 1; máximo 1.
13. TEXT: mínimo 0; máximo 1; sem opções.

REGRAS PARA AUDITORIA:
1. Não invente fatos.
2. Identifique produtos sem descrição, ingredientes, palavras-chave ou sinônimos.
3. Identifique nomes possivelmente duplicados ou muito parecidos.
4. Identifique categorias muito semelhantes.
5. Informe se não existem bebidas, sobremesas ou acompanhamentos, mas trate isso como recomendação, não erro.
6. Gere no máximo 8 recomendações.
7. Prioridades: alta, media ou baixa.
8. Tipos: qualidade, duplicidade, categoria, oportunidade ou configuracao.
9. Retorne sempre todos os produtos recebidos, mesmo sem grupos sugeridos.
10. Retorne somente o JSON do schema.
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
                            'grupos_sugeridos' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'additionalProperties' => false,
                                    'properties' => [
                                        'nome' => [
                                            'type' => 'string',
                                        ],
                                        'tipo' => [
                                            'type' => 'string',
                                            'enum' => [
                                                'CHECKBOX',
                                                'RADIO',
                                                'TEXT',
                                            ],
                                        ],
                                        'minimo' => [
                                            'type' => 'integer',
                                            'minimum' => 0,
                                            'maximum' => 5,
                                        ],
                                        'maximo' => [
                                            'type' => 'integer',
                                            'minimum' => 1,
                                            'maximum' => 5,
                                        ],
                                        'justificativa' => [
                                            'type' => 'string',
                                        ],
                                        'opcoes' => [
                                            'type' => 'array',
                                            'items' => [
                                                'type' => 'object',
                                                'additionalProperties' => false,
                                                'properties' => [
                                                    'nome' => [
                                                        'type' => 'string',
                                                    ],
                                                    'valor' => [
                                                        'type' => 'number',
                                                        'minimum' => 0,
                                                        'maximum' => 0,
                                                    ],
                                                ],
                                                'required' => [
                                                    'nome',
                                                    'valor',
                                                ],
                                            ],
                                        ],
                                    ],
                                    'required' => [
                                        'nome',
                                        'tipo',
                                        'minimo',
                                        'maximo',
                                        'justificativa',
                                        'opcoes',
                                    ],
                                ],
                            ],
                        ],
                        'required' => [
                            'indice',
                            'grupos_sugeridos',
                        ],
                    ],
                ],
                'auditoria' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'resumo' => [
                            'type' => 'string',
                        ],
                        'pontuacao' => [
                            'type' => 'integer',
                            'minimum' => 0,
                            'maximum' => 100,
                        ],
                        'recomendacoes' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'additionalProperties' => false,
                                'properties' => [
                                    'tipo' => [
                                        'type' => 'string',
                                        'enum' => [
                                            'qualidade',
                                            'duplicidade',
                                            'categoria',
                                            'oportunidade',
                                            'configuracao',
                                        ],
                                    ],
                                    'prioridade' => [
                                        'type' => 'string',
                                        'enum' => [
                                            'alta',
                                            'media',
                                            'baixa',
                                        ],
                                    ],
                                    'titulo' => [
                                        'type' => 'string',
                                    ],
                                    'descricao' => [
                                        'type' => 'string',
                                    ],
                                    'produtos_afetados' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'string',
                                        ],
                                    ],
                                ],
                                'required' => [
                                    'tipo',
                                    'prioridade',
                                    'titulo',
                                    'descricao',
                                    'produtos_afetados',
                                ],
                            ],
                        ],
                    ],
                    'required' => [
                        'resumo',
                        'pontuacao',
                        'recomendacoes',
                    ],
                ],
            ],
            'required' => [
                'produtos',
                'auditoria',
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
                        'A IA recusou a análise deste cardápio.'
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
            'A IA não retornou a análise.'
        );
    }

    private function normalizarResultado(
        array $linhas,
        array $dados
    ): array {
        foreach ((array) ($dados['produtos'] ?? []) as $produto) {
            $indice = $produto['indice'] ?? null;

            if (
                !is_int($indice)
                || !array_key_exists($indice, $linhas)
            ) {
                continue;
            }

            $linhas[$indice]['grupos_sugeridos'] =
                $this->normalizarGrupos(
                    $produto['grupos_sugeridos'] ?? []
                );
        }

        foreach ($linhas as $indice => $linha) {
            $linhas[$indice]['grupos_sugeridos'] =
                array_values(
                    (array) (
                        $linha['grupos_sugeridos'] ?? []
                    )
                );
        }

        $auditoria = (array) ($dados['auditoria'] ?? []);

        return [
            'produtos' => array_values($linhas),
            'auditoria' => [
                'resumo' => trim(
                    (string) ($auditoria['resumo'] ?? '')
                ),
                'pontuacao' => min(
                    100,
                    max(
                        0,
                        (int) ($auditoria['pontuacao'] ?? 0)
                    )
                ),
                'recomendacoes' => array_values(
                    (array) (
                        $auditoria['recomendacoes'] ?? []
                    )
                ),
            ],
        ];
    }

    private function normalizarGrupos(mixed $grupos): array
    {
        if (!is_array($grupos)) {
            return [];
        }

        $resultado = [];

        foreach (array_slice($grupos, 0, 3) as $grupo) {
            $tipo = strtoupper(
                trim((string) ($grupo['tipo'] ?? 'CHECKBOX'))
            );

            if (!in_array(
                $tipo,
                ['CHECKBOX', 'RADIO', 'TEXT'],
                true
            )) {
                continue;
            }

            $minimo = (int) ($grupo['minimo'] ?? 0);
            $maximo = (int) ($grupo['maximo'] ?? 1);

            if ($tipo === 'RADIO') {
                $minimo = 1;
                $maximo = 1;
            } elseif ($tipo === 'TEXT') {
                $minimo = 0;
                $maximo = 1;
            } else {
                $minimo = 0;
                $maximo = min(5, max(1, $maximo));
            }

            $opcoes = [];

            if ($tipo !== 'TEXT') {
                foreach (
                    array_slice(
                        (array) ($grupo['opcoes'] ?? []),
                        0,
                        8
                    )
                    as $opcao
                ) {
                    $nomeOpcao = trim(
                        (string) ($opcao['nome'] ?? '')
                    );

                    if ($nomeOpcao !== '') {
                        $opcoes[] = [
                            'nome' => $nomeOpcao,
                            'valor' => 0.0,
                        ];
                    }
                }
            }

            $resultado[] = [
                'nome' => trim(
                    (string) ($grupo['nome'] ?? '')
                ),
                'tipo' => $tipo,
                'minimo' => $minimo,
                'maximo' => $maximo,
                'justificativa' => trim(
                    (string) (
                        $grupo['justificativa'] ?? ''
                    )
                ),
                'opcoes' => $opcoes,
            ];
        }

        return $resultado;
    }
}