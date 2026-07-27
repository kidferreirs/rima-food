<?php

namespace App\Services\AI\Conversation;

use App\Models\Restaurante;
use App\Services\AI\Knowledge\KnowledgeEngine;
use Illuminate\Support\Str;

class ConversationEngine
{
    public function __construct(
        private readonly KnowledgeEngine $knowledge,
        private readonly ConversationInterpreter $interpreter,
    ) {
    }

    /**
     * Processa uma mensagem do cliente e decide a próxima ação.
     */
    public function processar(
        Restaurante $restaurante,
        string $mensagem,
        ConversationContext $contexto
    ): array {
        $texto = $this->normalizar($mensagem);
        /*
        |--------------------------------------------------------------------------
        | Resposta à oferta do cardápio
        |--------------------------------------------------------------------------
        */

        if ($contexto->estado === ConversationContext::ESTADO_OFERECENDO_CARDAPIO) {
            if ($this->aceitouVerCardapio($texto)) {
                $contexto->intent = 'ver_cardapio';
                $contexto->estado = ConversationContext::ESTADO_ATENDIMENTO;

                return $this->resposta(
                    'Claro! Veja nosso cardápio completo no link abaixo.',
                    $contexto,
                    ConversationAction::ENVIAR_CARDAPIO,
                    [],
                    [
                        'slug' => $restaurante->slug,
                    ]
                );
            }

            if ($this->recusouVerCardapio($texto)) {
                $contexto->intent = 'recusar_cardapio';
                $contexto->estado = ConversationContext::ESTADO_ATENDIMENTO;

                return $this->resposta(
                    'Sem problema. Como posso ajudar?',
                    $contexto,
                    ConversationAction::RESPOSTA_DESCONHECIDA
                );
            }

            /*
             * Caso o cliente faça outra pergunta, a conversa continua
             * normalmente sem obrigá-lo a responder sim ou não.
             */
            $contexto->estado = ConversationContext::ESTADO_ATENDIMENTO;
        }

        /*
        |--------------------------------------------------------------------------
        | Confirmação do pedido
        |--------------------------------------------------------------------------
        */

        if ($contexto->estado === ConversationContext::ESTADO_AGUARDANDO_CONFIRMACAO) {
            if ($this->ehConfirmacao($texto)) {
                $contexto->intent = 'confirmar_pedido';
                $contexto->estado = ConversationContext::ESTADO_FINALIZADO;
                $contexto->pedidoFinalizado = true;

                return $this->resposta(
                    'Pedido confirmado! Agora vamos continuar para os dados de entrega e pagamento.',
                    $contexto,
                    'pedido_confirmado'
                );
            }

            if ($this->ehNegacao($texto)) {
                $contexto->intent = 'alterar_pedido';
                $contexto->estado = ConversationContext::ESTADO_MONTANDO_PEDIDO;

                return $this->resposta(
                    'Certo. O que você deseja alterar no pedido?',
                    $contexto,
                    'alterar_pedido'
                );
            }

            return $this->resposta(
                'Deseja confirmar o pedido? Responda sim ou diga o que gostaria de alterar.',
                $contexto,
                'aguardando_confirmacao'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Saudação
        |--------------------------------------------------------------------------
        */

        if ($this->ehSaudacao($texto)) {
            $contexto->intent = 'saudacao';
            $contexto->estado = ConversationContext::ESTADO_OFERECENDO_CARDAPIO;

            return $this->resposta(
                "Olá! Seja bem-vindo à {$restaurante->nome}. Gostaria de ver nosso cardápio?",
                $contexto,
                ConversationAction::SAUDACAO
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Envio direto do cardápio
        |--------------------------------------------------------------------------
        */

        if ($this->querLinkCardapio($texto)) {
            $contexto->intent = 'ver_cardapio';
            $contexto->estado = ConversationContext::ESTADO_ATENDIMENTO;

            return $this->resposta(
                'Claro! Veja nosso cardápio completo no link abaixo.',
                $contexto,
                ConversationAction::ENVIAR_CARDAPIO,
                [],
                [
                    'slug' => $restaurante->slug,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Categorias disponíveis
        |--------------------------------------------------------------------------
        */

        if ($this->querVerCategorias($texto)) {
            $contexto->intent = 'listar_categorias';

            $categorias = $this->knowledge
                ->categorias()
                ->listar($restaurante);

            if (empty($categorias)) {
                return $this->resposta(
                    'O cardápio ainda não possui categorias disponíveis.',
                    $contexto,
                    ConversationAction::LISTAR_CATEGORIAS
                );
            }

            $nomes = collect($categorias)
                ->pluck('nome')
                ->implode(', ');

            return $this->resposta(
                "Temos: {$nomes}. Qual categoria você deseja ver?",
                $contexto,
                ConversationAction::CATEGORIAS_ENCONTRADAS,
                [],
                $categorias
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Informações do restaurante
        |--------------------------------------------------------------------------
        */

        if ($this->querInformacaoRestaurante($texto)) {
            $contexto->intent = 'informacao_restaurante';

            return $this->responderInformacaoRestaurante(
                $restaurante,
                $texto,
                $contexto
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Finalização
        |--------------------------------------------------------------------------
        */

        if ($this->querFinalizar($texto)) {
            $contexto->intent = 'finalizar_pedido';

            if (empty($contexto->itens)) {
                return $this->resposta(
                    'Seu pedido ainda está vazio. O que você gostaria de adicionar?',
                    $contexto,
                    'pedido_vazio'
                );
            }

            $contexto->estado = ConversationContext::ESTADO_AGUARDANDO_CONFIRMACAO;

            return $this->resposta(
                $this->montarResumo($contexto),
                $contexto,
                'aguardando_confirmacao'
            );
        }

        /*
|--------------------------------------------------------------------------
| Interpretação contextual
|--------------------------------------------------------------------------
*/

        $interpretacao = $this->interpreter->interpretar(
            $mensagem,
            $contexto
        );

        if (
            $interpretacao['intent']
            === ConversationInterpreter::REPETIR_PRODUTO
        ) {
            if ($contexto->produto === null) {
                return $this->resposta(
                    'Qual produto você gostaria de repetir?',
                    $contexto,
                    ConversationAction::PRODUTO_NAO_ENCONTRADO
                );
            }

            $quantidade = $interpretacao['quantidade'];

            $this->adicionarItem(
                $contexto,
                $contexto->produto,
                $quantidade
            );

            return $this->resposta(
                $quantidade === 1
                ? "Adicionei mais um {$contexto->produto['nome']}. Deseja mais alguma coisa?"
                : "Adicionei mais {$quantidade} unidades de {$contexto->produto['nome']}. Deseja mais alguma coisa?",
                $contexto,
                ConversationAction::PRODUTO_ADICIONADO,
                [$contexto->produto['id']],
                [$contexto->produto]
            );
        }

        if (
            $interpretacao['intent']
            === ConversationInterpreter::ADICIONAR_OBSERVACAO
        ) {
            $observacao = $interpretacao['observacao'];

            $this->adicionarObservacaoUltimoItem(
                $contexto,
                $observacao
            );

            return $this->resposta(
                "Certo! Anotei: {$observacao}. Deseja mais alguma coisa?",
                $contexto,
                'observacao_adicionada'
            );
        }

        if (
            $interpretacao['intent']
            === ConversationInterpreter::REMOVER_ULTIMO_ITEM
        ) {
            $itemRemovido = $this->removerUltimoItem($contexto);

            if ($itemRemovido === null) {
                return $this->resposta(
                    'Seu pedido ainda está vazio.',
                    $contexto,
                    ConversationAction::PEDIDO_VAZIO
                );
            }

            return $this->resposta(
                "{$itemRemovido['nome']} removido do pedido. Deseja mais alguma coisa?",
                $contexto,
                'produto_removido'
            );
        }

        if (
            $interpretacao['intent']
            === ConversationInterpreter::ADICIONAR_PRODUTO_QUANTIDADE
        ) {
            $produtos = $this->knowledge
                ->produtos()
                ->buscar(
                    $restaurante,
                    $interpretacao['termo_produto']
                );

            if (empty($produtos)) {
                return $this->resposta(
                    'Não encontrei esse produto no cardápio.',
                    $contexto,
                    ConversationAction::PRODUTO_NAO_ENCONTRADO
                );
            }

            if (count($produtos) > 1) {
                $contexto->intent = 'escolher_produto';
                $contexto->estado =
                    ConversationContext::ESTADO_ESCOLHENDO_PRODUTO;

                $lista = collect($produtos)
                    ->take(5)
                    ->map(
                        fn(array $produto) =>
                        "{$produto['nome']} — {$this->formatarPreco($produto['preco'])}"
                    )
                    ->implode(', ');

                return $this->resposta(
                    "Encontrei estas opções: {$lista}. Qual você deseja?",
                    $contexto,
                    ConversationAction::MULTIPLOS_PRODUTOS,
                    array_column($produtos, 'id'),
                    $produtos
                );
            }

            $produto = $produtos[0];
            $quantidade = $interpretacao['quantidade'];

            $contexto->produto = $produto;
            $contexto->intent = 'adicionar_produto';
            $contexto->estado =
                ConversationContext::ESTADO_MONTANDO_PEDIDO;

            $this->adicionarItem(
                $contexto,
                $produto,
                $quantidade
            );

            return $this->resposta(
                "{$quantidade}x {$produto['nome']} adicionado ao pedido. Deseja mais alguma coisa?",
                $contexto,
                ConversationAction::PRODUTO_ADICIONADO,
                [$produto['id']],
                [$produto]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Busca de produto
        |--------------------------------------------------------------------------
        */

        $termoProduto = $this->extrairTermoProduto($mensagem);

        $produtos = $this->knowledge
            ->produtos()
            ->buscar($restaurante, $termoProduto);

        if (empty($produtos)) {
            $contexto->intent = 'produto_nao_encontrado';

            return $this->resposta(
                'Não encontrei esse item no cardápio. Você pode informar o nome do produto?',
                $contexto,
                'produto_nao_encontrado'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Mais de um produto encontrado
        |--------------------------------------------------------------------------
        */

        if (count($produtos) > 1) {
            $contexto->intent = 'escolher_produto';
            $contexto->estado = ConversationContext::ESTADO_ESCOLHENDO_PRODUTO;

            $lista = collect($produtos)
                ->take(5)
                ->map(
                    fn(array $produto) =>
                    "{$produto['nome']} — {$this->formatarPreco($produto['preco'])}"
                )
                ->implode(', ');

            return $this->resposta(
                "Encontrei estas opções: {$lista}. Qual você deseja?",
                $contexto,
                'multiplos_produtos',
                array_column($produtos, 'id'),
                $produtos
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Produto único encontrado
        |--------------------------------------------------------------------------
        */

        $produto = $produtos[0];

        $contexto->intent = 'adicionar_produto';
        $contexto->produto = $produto;
        $contexto->estado = ConversationContext::ESTADO_MONTANDO_PEDIDO;

        $this->adicionarItem($contexto, $produto);

        return $this->resposta(
            "{$produto['nome']} adicionado ao pedido. Deseja mais alguma coisa?",
            $contexto,
            'produto_adicionado',
            [$produto['id']],
            [$produto]
        );
    }

    /**
     * Adiciona um produto à memória do pedido.
     */
    private function adicionarItem(
        ConversationContext $contexto,
        array $produto,
        int $quantidade = 1
    ): void {
        $contexto->pedido->adicionarProduto(
            $produto,
            $quantidade
        );

        $contexto->itens = $contexto->pedido->itens();
    }

    /**
     * Retorna informações do restaurante.
     */
    private function responderInformacaoRestaurante(
        Restaurante $restaurante,
        string $texto,
        ConversationContext $contexto
    ): array {
        $dados = $this->knowledge
            ->restaurante()
            ->dados($restaurante);

        if (Str::contains($texto, ['horario', 'abre', 'fecha', 'funcionamento'])) {
            return $this->resposta(
                "Funcionamos das {$dados['horario']['abre']} às {$dados['horario']['fecha']}.",
                $contexto,
                'informacao_horario'
            );
        }

        if (Str::contains($texto, ['delivery', 'entrega', 'entregam'])) {
            $mensagem = $dados['delivery']
                ? "Sim, fazemos delivery. O tempo médio é de {$dados['tempo_medio']} minutos."
                : 'No momento não trabalhamos com delivery.';

            return $this->resposta(
                $mensagem,
                $contexto,
                'informacao_delivery'
            );
        }

        if (Str::contains($texto, ['retirada', 'retirar', 'buscar'])) {
            $mensagem = $dados['retirada']
                ? 'Sim, você pode retirar o pedido no restaurante.'
                : 'No momento não trabalhamos com retirada.';

            return $this->resposta(
                $mensagem,
                $contexto,
                'informacao_retirada'
            );
        }

        if (Str::contains($texto, ['endereco', 'localizacao', 'onde fica'])) {
            return $this->resposta(
                "Estamos em {$dados['endereco']}.",
                $contexto,
                'informacao_endereco'
            );
        }

        return $this->resposta(
            "O tempo médio é de {$dados['tempo_medio']} minutos. Trabalhamos com delivery, retirada e consumo no local conforme disponibilidade.",
            $contexto,
            'informacao_restaurante'
        );
    }

    private function adicionarObservacaoUltimoItem(
        ConversationContext $contexto,
        string $observacao
    ): void {
        $contexto->pedido
            ->adicionarObservacaoUltimoProduto($observacao);

        $contexto->itens = $contexto->pedido->itens();
    }

    private function removerUltimoItem(
        ConversationContext $contexto
    ): ?array {
        $item = $contexto->pedido
            ->removerUltimoProduto();

        $contexto->itens = $contexto->pedido->itens();
        $contexto->produto = null;

        return $item;
    }

    /**
     * Monta o resumo antes da confirmação.
     */
    private function montarResumo(ConversationContext $contexto): string
    {
        return $contexto->pedido->resumo();
    }

    private function ehSaudacao(string $texto): bool
    {
        return in_array($texto, [
            'oi',
            'ola',
            'bom dia',
            'boa tarde',
            'boa noite',
            'e ai',
        ], true);
    }

    private function aceitouVerCardapio(string $texto): bool
    {
        return in_array($texto, [
            'sim',
            'quero',
            'claro',
            'pode ser',
            'pode mandar',
            'manda',
            'mostra',
            'ok',
            'beleza',
        ], true);
    }

    private function recusouVerCardapio(string $texto): bool
    {
        return in_array($texto, [
            'nao',
            'agora nao',
            'nao quero',
            'dispenso',
        ], true);
    }

    private function querLinkCardapio(string $texto): bool
    {
        return Str::contains($texto, [
            'manda o cardapio',
            'mandar o cardapio',
            'ver o cardapio',
            'quero o cardapio',
            'link do cardapio',
            'link do menu',
            'tem cardapio',
            'tem menu',
            'abrir cardapio',
            'abrir menu',
            'onde vejo os produtos',
        ]);
    }

    private function querVerCategorias(string $texto): bool
    {
        return Str::contains($texto, [
            'categorias',
            'quais categorias',
            'o que tem',
            'quais opcoes',
            'tipos de comida',
            'tipos de lanche',
        ]);
    }

    private function querInformacaoRestaurante(string $texto): bool
    {
        return Str::contains($texto, [
            'horario',
            'abre',
            'fecha',
            'funcionamento',
            'delivery',
            'entrega',
            'retirada',
            'retirar',
            'endereco',
            'localizacao',
            'onde fica',
        ]);
    }

    private function querFinalizar(string $texto): bool
    {
        return Str::contains($texto, [
            'finalizar',
            'fechar pedido',
            'concluir pedido',
            'terminar pedido',
            'so isso',
            'pode fechar',
        ]);
    }

    private function ehConfirmacao(string $texto): bool
    {
        return in_array($texto, [
            'sim',
            'confirmo',
            'pode confirmar',
            'pode fechar',
            'fechado',
            'confirmar',
        ], true);
    }

    private function ehNegacao(string $texto): bool
    {
        return Str::contains($texto, [
            'nao',
            'cancelar',
            'alterar',
            'mudar',
            'voltar',
        ]);
    }

    /**
     * Remove palavras de intenção e mantém o nome provável do produto.
     */
    private function extrairTermoProduto(string $mensagem): string
    {
        $texto = $this->normalizar($mensagem);

        $remover = [
            'eu quero',
            'quero pedir',
            'quero',
            'gostaria de',
            'me ve',
            'me da',
            'manda',
            'coloca',
            'adiciona',
            'adicionar',
            'tem',
            'vocês tem',
            'voces tem',
            'um',
            'uma',
            'por favor',
        ];

        $texto = str_replace($remover, ' ', $texto);
        $texto = preg_replace('/\s+/', ' ', $texto);

        return trim($texto);
    }

    private function normalizar(string $mensagem): string
    {
        return Str::of($mensagem)
            ->lower()
            ->ascii()
            ->trim()
            ->toString();
    }

    private function formatarPreco(float $preco): string
    {
        return 'R$ ' . number_format($preco, 2, ',', '.');
    }

    private function resposta(
        string $mensagem,
        ConversationContext $contexto,
        string $acao,
        array $produtoIds = [],
        array $dados = []
    ): array {
        return [
            'mensagem' => $mensagem,
            'acao' => $acao,
            'produto_ids' => $produtoIds,
            'dados' => $dados,
            'contexto' => $contexto,
        ];
    }
}