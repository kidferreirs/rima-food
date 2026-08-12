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
        private readonly ProductOptionMatcher $productOptionMatcher,
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
        | Confirmação para adicionar produto encontrado
        |--------------------------------------------------------------------------
        */

        if ($contexto->estado === ConversationContext::ESTADO_AGUARDANDO_ADICIONAR_PRODUTO) {
            if ($this->ehConfirmacao($texto)) {
                $produto = $contexto->produto;

                if ($produto === null) {
                    $contexto->estado =
                        ConversationContext::ESTADO_ATENDIMENTO;

                    return $this->resposta(
                        'Não consegui localizar o produto. Qual item você deseja?',
                        $contexto,
                        'produto_nao_encontrado'
                    );
                }

                $this->adicionarItem(
                    $contexto,
                    $produto,
                    1
                );

                $contexto->intent = 'adicionar_produto';
                $contexto->estado =
                    ConversationContext::ESTADO_MONTANDO_PEDIDO;

                /*
                 * Verifica opções obrigatórias do produto.
                 */
                $contexto->faltando = $this->productOptionMatcher
                    ->gruposObrigatorios(
                        (int) $produto['id']
                    );

                $grupoObrigatorio =
                    $contexto->faltando[0] ?? null;

                if ($grupoObrigatorio !== null) {
                    return $this->resposta(
                        $this->productOptionMatcher
                            ->montarPerguntaGrupo($grupoObrigatorio),
                        $contexto,
                        ConversationAction::PERGUNTAR_OPCAO_OBRIGATORIA
                    );
                }

                return $this->resposta(
                    "{$produto['nome']} foi adicionado ao seu pedido.\n\n"
                    . "Deseja mais alguma coisa ou prefere finalizar?",
                    $contexto,
                    'produto_adicionado',
                    [(int) $produto['id']],
                    [$produto]
                );
            }

            if ($this->ehNegacao($texto)) {
                $contexto->produto = null;
                $contexto->intent = 'recusar_produto';
                $contexto->estado =
                    ConversationContext::ESTADO_ATENDIMENTO;

                return $this->resposta(
                    'Sem problema. O que mais você gostaria de ver?',
                    $contexto,
                    'produto_recusado'
                );
            }

            return $this->resposta(
                'Deseja adicionar esse produto ao pedido? Responda sim ou não.',
                $contexto,
                'aguardando_adicao_produto'
            );
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
        | Intenção de iniciar pedido
        |--------------------------------------------------------------------------
        */

        $textoNormalizado = $this->normalizar($mensagem);

        if ($this->querIniciarPedido($textoNormalizado)) {
            $contexto->intent = 'iniciar_pedido';
            $contexto->estado = ConversationContext::ESTADO_MONTANDO_PEDIDO;

            return $this->resposta(
                'Claro! 😄 O que você gostaria de pedir?',
                $contexto,
                'iniciar_pedido'
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
    | Resposta de grupo obrigatório
    |--------------------------------------------------------------------------
    */

        if (
            !empty($contexto->faltando)
            && $contexto->produto !== null
        ) {
            $grupoAtual = $contexto->faltando[0];

            $item = $contexto->pedido->ultimoItem();

            if ($item === null) {
                $contexto->faltando = [];

                return $this->resposta(
                    'Não consegui localizar o produto que estava sendo configurado.',
                    $contexto,
                    ConversationAction::RESPOSTA_DESCONHECIDA
                );
            }

            $validacao = $this->productOptionMatcher
                ->validarRespostaGrupo(
                    $grupoAtual,
                    $mensagem,
                    $item
                );

            if (!$validacao['valido']) {
                return $this->resposta(
                    $validacao['mensagem'],
                    $contexto,
                    ConversationAction::OPCAO_OBRIGATORIA_INVALIDA
                );
            }

            $maximo = max(
                1,
                (int) ($grupoAtual['maximo'] ?? 1)
            );

            /*
             * Grupos de escolha única substituem uma seleção anterior.
             */
            if ($maximo === 1) {
                $item->removerOpcoesGrupo(
                    (int) $grupoAtual['id']
                );
            }

            foreach ($validacao['opcoes'] as $opcao) {
                $item->adicionarOpcao($opcao);
            }

            $contexto->itens = $contexto->pedido->itens();

            if (
                !$this->productOptionMatcher
                    ->grupoEstaCompleto($grupoAtual, $item)
            ) {
                return $this->resposta(
                    $this->productOptionMatcher
                        ->montarPerguntaGrupo($grupoAtual),
                    $contexto,
                    ConversationAction::PERGUNTAR_OPCAO_OBRIGATORIA
                );
            }

            /*
             * Remove o grupo concluído da fila.
             */
            array_shift($contexto->faltando);

            /*
             * Carrega os demais grupos obrigatórios apenas na primeira conclusão.
             */
            if (empty($contexto->faltando)) {
                $gruposObrigatorios = $this->productOptionMatcher
                    ->gruposObrigatorios(
                        (int) $contexto->produto['id']
                    );

                $contexto->faltando = array_values(
                    array_filter(
                        $gruposObrigatorios,
                        fn(array $grupo): bool =>
                        !$this->productOptionMatcher
                            ->grupoEstaCompleto($grupo, $item)
                    )
                );
            }

            if (!empty($contexto->faltando)) {
                $proximoGrupo = $contexto->faltando[0];

                return $this->resposta(
                    $this->productOptionMatcher
                        ->montarPerguntaGrupo($proximoGrupo),
                    $contexto,
                    ConversationAction::PERGUNTAR_OPCAO_OBRIGATORIA
                );
            }

            return $this->resposta(
                "{$contexto->produto['nome']} configurado com sucesso. Deseja mais alguma coisa?",
                $contexto,
                ConversationAction::OPCAO_OBRIGATORIA_SELECIONADA
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Remoção de ingredientes do último produto
        |--------------------------------------------------------------------------
        */

        if (
            $contexto->produto !== null
            && $this->possuiIntencaoRemoverIngrediente($texto)
        ) {
            $produtoCompleto = $this->knowledge
                ->produtos()
                ->encontrar(
                    $restaurante,
                    (int) $contexto->produto['id']
                );

            $ingredientes = is_array(
                $produtoCompleto['ingredientes'] ?? null
            )
                ? $produtoCompleto['ingredientes']
                : [];

            $ingredientesRemovidos =
                $this->encontrarIngredientesNaMensagem(
                    $texto,
                    $ingredientes
                );

            if (empty($ingredientesRemovidos)) {
                return $this->resposta(
                    'Qual ingrediente você deseja retirar?',
                    $contexto,
                    'ingrediente_nao_identificado'
                );
            }

            foreach ($ingredientesRemovidos as $ingrediente) {
                $contexto->pedido
                    ->removerIngredienteUltimoProduto(
                        $ingrediente
                    );
            }

            $contexto->itens = $contexto->pedido->itens();

            $nomes = implode(', ', $ingredientesRemovidos);

            return $this->resposta(
                "Certo! {$nomes} será removido do pedido. Deseja mais alguma coisa?",
                $contexto,
                'ingredientes_removidos'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Interpretação contextual
        |--------------------------------------------------------------------------
        */
        $interpretacao = $this->interpreter->interpretar($mensagem, $contexto);

        /*
        |--------------------------------------------------------------------------
        | Consulta ao cardápio
        |--------------------------------------------------------------------------
        */
        if ($interpretacao['intent'] === ConversationInterpreter::CONSULTAR_PRODUTO) {
            return $this->responderConsultaCardapio(
                $restaurante,
                $contexto,
                $interpretacao['termo_produto']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Opções do último produto
        |--------------------------------------------------------------------------
        */
        if ($contexto->produto !== null) {
            $opcoes = $this->productOptionMatcher
                ->encontrarOpcoes(
                    $contexto->produto['id'],
                    $mensagem
                );

            if (!empty($opcoes)) {
                $contexto->pedido
                    ->adicionarOpcoesUltimoProduto($opcoes);
                $contexto->itens =
                    $contexto->pedido->itens();
                $nomes = collect($opcoes)
                    ->pluck('nome')
                    ->implode(', ');
                return $this->resposta(
                    "{$nomes} adicionados ao pedido. Deseja mais alguma coisa?",
                    $contexto,
                    'opcoes_adicionadas'
                );
            }
        }

        if ($interpretacao['intent'] === ConversationInterpreter::REPETIR_PRODUTO) {
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

        if ($interpretacao['intent'] === ConversationInterpreter::ADICIONAR_OBSERVACAO) {
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

        if ($interpretacao['intent'] === ConversationInterpreter::REMOVER_ULTIMO_ITEM) {
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

        if ($interpretacao['intent'] === ConversationInterpreter::ADICIONAR_PRODUTO_QUANTIDADE) {
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

            $grupoObrigatorio = $this->productOptionMatcher
                ->primeiroGrupoObrigatorio(
                    $produto['id']
                );

            $contexto->faltando = $this->productOptionMatcher
                ->gruposObrigatorios(
                    (int) $produto['id']
                );

            $grupoObrigatorio = $contexto->faltando[0] ?? null;

            if ($grupoObrigatorio !== null) {
                return $this->resposta(
                    $this->productOptionMatcher
                        ->montarPerguntaGrupo($grupoObrigatorio),
                    $contexto,
                    ConversationAction::PERGUNTAR_OPCAO_OBRIGATORIA
                );
            }

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

        $contexto->faltando = $this->productOptionMatcher
            ->gruposObrigatorios(
                (int) $produto['id']
            );

        $grupoObrigatorio = $contexto->faltando[0] ?? null;

        if ($grupoObrigatorio !== null) {
            return $this->resposta(
                $this->productOptionMatcher
                    ->montarPerguntaGrupo($grupoObrigatorio),
                $contexto,
                ConversationAction::PERGUNTAR_OPCAO_OBRIGATORIA
            );
        }

        return $this->resposta(
            "{$produto['nome']} adicionado ao pedido. Deseja mais alguma coisa?",
            $contexto,
            'produto_adicionado',
            [$produto['id']],
            [$produto]
        );
    }

    private function responderConsultaCardapio(
        Restaurante $restaurante,
        ConversationContext $contexto,
        string $termo
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Procura produto
        |--------------------------------------------------------------------------
        */
        $produtos = $this->knowledge
            ->produtos()
            ->buscar($restaurante, $termo);

        if (!empty($produtos)) {
            if (count($produtos) === 1) {
                $produto = $produtos[0];

                $contexto->produto = $produto;
                $contexto->intent = 'confirmar_adicao_produto';
                $contexto->estado =
                    ConversationContext::ESTADO_AGUARDANDO_ADICIONAR_PRODUTO;

                return $this->resposta(
                    "*Encontrei, temos:*\n\n"
                    . "{$produto['nome']}\n"
                    . $this->formatarPreco($produto['preco'])
                    . "\n\nDeseja adicionar ao pedido?",
                    $contexto,
                    'produto_encontrado',
                    [$produto['id']],
                    [$produto]
                );
            }

            $lista = collect($produtos)
                ->take(8)
                ->map(
                    fn(array $produto) =>
                    "{$produto['nome']} — "
                    . $this->formatarPreco($produto['preco'])
                )
                ->implode("\n");

            return $this->resposta(
                "*Encontrei, temos:*\n\n{$lista}",
                $contexto,
                ConversationAction::MULTIPLOS_PRODUTOS,
                array_column($produtos, 'id'),
                $produtos
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Procura categoria
        |--------------------------------------------------------------------------
        */

        $categoria = $this->knowledge
            ->categorias()
            ->buscarPorTermo(
                $restaurante,
                $termo
            );

        if ($categoria !== null) {

            $produtosCategoria = $this->knowledge
                ->produtos()
                ->porCategoria(
                    $restaurante,
                    (int) $categoria['id']
                );

            if (!empty($produtosCategoria)) {

                $lista = collect($produtosCategoria)
                    ->map(
                        fn(array $produto) =>
                        "{$produto['nome']} — "
                        . $this->formatarPreco($produto['preco'])
                    )
                    ->implode("\n");

                return $this->resposta(
                    "*Encontrei, temos:*\n\n{$lista}",
                    $contexto,
                    ConversationAction::CATEGORIAS_ENCONTRADAS,
                    array_column($produtosCategoria, 'id'),
                    $produtosCategoria
                );
            }
        }

        return $this->resposta(
            'Não encontrei esse item no cardápio.',
            $contexto,
            ConversationAction::PRODUTO_NAO_ENCONTRADO
        );
    }

    /*** Adiciona um produto à memória do pedido.*/
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

    private function querIniciarPedido(string $texto): bool
    {
        return Str::contains($texto, [
            'quero fazer um pedido',
            'quero fazer pedido',
            'quero pedir',
            'fazer um pedido',
            'fazer pedido',
            'gostaria de fazer um pedido',
            'gostaria de pedir',
            'quero comprar',
            'vou fazer um pedido',
            'posso fazer um pedido',
            'como faco um pedido',
        ]);
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

    private function possuiIntencaoRemoverIngrediente(
        string $texto
    ): bool {
        return Str::contains($texto, [
            'sem ',
            'tirar ',
            'tira ',
            'retirar ',
            'remove ',
            'remover ',
            'nao quero ',
            'não quero ',
        ]);
    }

    private function encontrarIngredientesNaMensagem(
        string $texto,
        array $ingredientes
    ): array {
        $encontrados = [];

        foreach ($ingredientes as $ingrediente) {
            $nome = is_array($ingrediente)
                ? ($ingrediente['nome'] ?? null)
                : $ingrediente;

            if (!$nome) {
                continue;
            }

            $nomeNormalizado = $this->normalizar(
                (string) $nome
            );

            /*
             * Permite "sem molho" encontrar "Molho especial".
             */
            $palavrasIngrediente = array_values(
                array_filter(
                    explode(' ', $nomeNormalizado),
                    fn(string $palavra): bool =>
                    mb_strlen($palavra) >= 4
                )
            );

            $encontrouNomeCompleto =
                Str::contains($texto, $nomeNormalizado);

            $encontrouPalavraPrincipal = collect(
                $palavrasIngrediente
            )->contains(
                    fn(string $palavra): bool =>
                    Str::contains($texto, $palavra)
                );

            if (
                $encontrouNomeCompleto
                || $encontrouPalavraPrincipal
            ) {
                $encontrados[] = (string) $nome;
            }
        }

        return array_values(array_unique($encontrados));
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