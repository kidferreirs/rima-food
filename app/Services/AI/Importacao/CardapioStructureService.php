<?php

namespace App\Services\AI\Importacao;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class CardapioStructureService
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
            20,
            (int) config('services.ai.timeout', 90)
        );
    }

    /**
     * Transforma o texto bruto em linhas prontas para o importador do Rima.
     *
     * @return array<int, array{
     *     categoria: string,
     *     nome: string,
     *     descricao: string,
     *     preco: float
     * }>
     */
    public function estruturar(string $texto): array
    {
        $texto = $this->normalizarTextoEntrada($texto);

        $this->validarConfiguracao();
        $this->validarTexto($texto);

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
                    $this->montarPayload($texto)
                );

            $response->throw();

            $conteudo = $this->extrairTextoResposta(
                $response->json()
            );

            $estrutura = $this->decodificarJson($conteudo);

            return $this->normalizarEstrutura($estrutura);
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'Não foi possível conectar ao serviço de IA. Tente novamente em alguns instantes.',
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
                    : 'O serviço de IA retornou um erro ao organizar o cardápio.',
                previous: $exception
            );
        } catch (RuntimeException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'Não foi possível organizar o cardápio com IA.',
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

        if ($this->baseUrl === '') {
            throw new RuntimeException(
                'A variável AI_BASE_URL não foi configurada.'
            );
        }
    }

    private function validarTexto(string $texto): void
    {
        $caracteres = mb_strlen(
            preg_replace('/\s+/u', '', $texto) ?? ''
        );

        if ($caracteres < 20) {
            throw new RuntimeException(
                'O texto extraído é muito curto para organizar o cardápio.'
            );
        }

        if (mb_strlen($texto) > 60000) {
            throw new RuntimeException(
                'O cardápio possui texto demais para esta primeira versão. Divida o arquivo em partes menores.'
            );
        }
    }

    private function montarPayload(string $texto): array
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
                        "Organize o cardápio abaixo.\n\n"
                        . "TEXTO EXTRAÍDO:\n"
                        . $texto,
                ],
            ],
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => 'cardapio_estruturado',
                    'strict' => true,
                    'schema' => $this->schema(),
                ],
            ],
            'temperature' => 0,
            'max_output_tokens' => 12000,
        ];
    }

    private function promptSistema(): string
    {
        return <<<'PROMPT'
Você é um especialista em interpretar cardápios brasileiros.

Sua única tarefa é transformar o texto extraído de um cardápio em categorias e produtos, sem inventar informações.

REGRAS OBRIGATÓRIAS:

1. Use somente informações presentes no texto.
2. Não invente produtos, preços, ingredientes ou descrições.
3. Ignore endereço, telefone, redes sociais, horários, chamadas de delivery, slogans e textos promocionais.
4. Não trate "MENU", "CARDÁPIO", "DELIVERY" ou nome do restaurante como categoria ou produto.
5. Preserve os nomes comerciais dos produtos.
6. Associe cada preço ao produto correto.
7. Converta preços brasileiros para número decimal:
   - "15,90" vira 15.90
   - "R$ 10" vira 10.00
8. Quando a descrição estiver claramente ligada ao produto, preserve-a.
9. Quando não houver descrição, use string vazia.
10. Quando a categoria estiver clara, use o título encontrado no cardápio.
11. Quando realmente não for possível identificar a categoria, use "Sem categoria".
12. Não duplique produtos idênticos dentro da mesma categoria.
13. Itens como bebidas, acompanhamentos, sobremesas, espetos, pizzas e lanches devem permanecer em suas categorias reais.
14. Não faça enriquecimento criativo. Sinônimos, palavras-chave e sugestões de grupos serão tratados depois.
15. Retorne somente a estrutura exigida pelo schema.
PROMPT;
    }

    private function schema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'categorias' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'nome' => [
                                'type' => 'string',
                            ],
                            'produtos' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'additionalProperties' => false,
                                    'properties' => [
                                        'nome' => [
                                            'type' => 'string',
                                        ],
                                        'descricao' => [
                                            'type' => 'string',
                                        ],
                                        'preco' => [
                                            'type' => 'number',
                                            'minimum' => 0,
                                        ],
                                    ],
                                    'required' => [
                                        'nome',
                                        'descricao',
                                        'preco',
                                    ],
                                ],
                            ],
                        ],
                        'required' => [
                            'nome',
                            'produtos',
                        ],
                    ],
                ],
            ],
            'required' => [
                'categorias',
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
                $tipo = data_get($conteudo, 'type');

                if ($tipo === 'refusal') {
                    $motivo = (string) data_get(
                        $conteudo,
                        'refusal',
                        'Solicitação recusada.'
                    );

                    throw new RuntimeException(
                        'A IA não conseguiu processar este cardápio: '
                        . $motivo
                    );
                }

                if ($tipo === 'output_text') {
                    $texto = data_get($conteudo, 'text');

                    if (is_string($texto) && trim($texto) !== '') {
                        return trim($texto);
                    }
                }
            }
        }

        throw new RuntimeException(
            'A IA não retornou conteúdo estruturado.'
        );
    }

    private function decodificarJson(string $conteudo): array
    {
        $conteudo = trim($conteudo);

        if (Str::startsWith($conteudo, '```')) {
            $conteudo = preg_replace(
                '/^```(?:json)?\s*|\s*```$/i',
                '',
                $conteudo
            ) ?? $conteudo;
        }

        $dados = json_decode(
            $conteudo,
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        if (!is_array($dados)) {
            throw new RuntimeException(
                'A IA retornou uma estrutura inválida.'
            );
        }

        return $dados;
    }

    private function normalizarEstrutura(array $estrutura): array
    {
        $linhas = [];
        $produtosVistos = [];

        foreach ((array) ($estrutura['categorias'] ?? []) as $categoria) {
            $nomeCategoria = $this->limparTexto(
                $categoria['nome'] ?? ''
            );

            if ($nomeCategoria === '') {
                $nomeCategoria = 'Sem categoria';
            }

            if ($this->deveIgnorarTexto($nomeCategoria)) {
                continue;
            }

            foreach ((array) ($categoria['produtos'] ?? []) as $produto) {
                $nomeProduto = $this->limparTexto(
                    $produto['nome'] ?? ''
                );

                if (
                    $nomeProduto === ''
                    || $this->deveIgnorarTexto($nomeProduto)
                ) {
                    continue;
                }

                $preco = $this->normalizarPreco(
                    $produto['preco'] ?? 0
                );

                if ($preco < 0) {
                    continue;
                }

                $chaveProduto =
                    $this->chaveComparacao($nomeCategoria)
                    . ':'
                    . $this->chaveComparacao($nomeProduto);

                if (isset($produtosVistos[$chaveProduto])) {
                    continue;
                }

                $linhas[] = [
                    'categoria' => $nomeCategoria,
                    'nome' => $nomeProduto,
                    'descricao' => $this->limparTexto(
                        $produto['descricao'] ?? ''
                    ),
                    'preco' => $preco,
                ];

                $produtosVistos[$chaveProduto] = true;
            }
        }

        if ($linhas === []) {
            throw new RuntimeException(
                'A IA não encontrou produtos válidos no cardápio.'
            );
        }

        return $linhas;
    }

    private function normalizarPreco(mixed $preco): float
    {
        if (is_int($preco) || is_float($preco)) {
            return round((float) $preco, 2);
        }

        $valor = trim((string) $preco);
        $valor = str_ireplace(['R$', ' '], '', $valor);

        if (
            str_contains($valor, ',')
            && str_contains($valor, '.')
        ) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        } elseif (str_contains($valor, ',')) {
            $valor = str_replace(',', '.', $valor);
        }

        $valor = preg_replace('/[^0-9.\-]/', '', $valor)
            ?? '0';

        return round((float) $valor, 2);
    }

    private function normalizarTextoEntrada(string $texto): string
    {
        $texto = str_replace(
            ["\r\n", "\r"],
            "\n",
            $texto
        );

        $texto = preg_replace(
            '/[ \t]+/u',
            ' ',
            $texto
        ) ?? $texto;

        $texto = preg_replace(
            '/\n{3,}/u',
            "\n\n",
            $texto
        ) ?? $texto;

        return trim($texto);
    }

    private function limparTexto(mixed $texto): string
    {
        $texto = trim((string) $texto);

        $texto = preg_replace(
            '/\s+/u',
            ' ',
            $texto
        ) ?? $texto;

        return Str::limit($texto, 1000, '');
    }

    private function chaveComparacao(string $texto): string
    {
        return Str::of($texto)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->toString();
    }

    private function deveIgnorarTexto(string $texto): bool
    {
        $normalizado = Str::of($texto)
            ->ascii()
            ->lower()
            ->squish()
            ->toString();

        if (preg_match('/\(?\d{2}\)?\s*\d{4,5}[-\s]?\d{4}/', $texto)) {
            return true;
        }

        if (
            preg_match(
                '/\b(rua|avenida|av\.|rodovia|travessa|cidade|cep)\b/i',
                $texto
            )
        ) {
            return true;
        }

        return in_array($normalizado, [
            'menu',
            'cardapio',
            'delivery',
            'peca pelo delivery',
            'telefone',
            'contato',
        ], true);
    }
}