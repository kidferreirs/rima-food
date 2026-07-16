<?php

namespace App\Services;

use App\Models\Restaurante;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GarcomCardapioService
{
    public function responder(
        Restaurante $restaurante,
        string $pergunta,
        array $historico = []
    ): array {
        $perguntaNormalizada = $this->normalizar($pergunta);

        $produtos = $restaurante->categorias
            ->flatMap(function ($categoria) {
                return $categoria->produtos->map(
                    function ($produto) use ($categoria) {
                        $produto->categoria_nome = $categoria->nome;

                        return $produto;
                    }
                );
            })
            ->values();

        if ($produtos->isEmpty()) {
            return $this->resposta(
                "😕 O cardápio ainda não possui produtos disponíveis.\n\n" .
                "Você pode entrar em contato diretamente com o restaurante para obter mais informações."
            );
        }

        if ($this->perguntaSobreCategorias($perguntaNormalizada)) {
            return $this->responderCategorias($restaurante);
        }

        if ($this->perguntaSobreMenorPreco($perguntaNormalizada)) {
            return $this->responderMenorPreco($produtos);
        }

        $limite = $this->extrairLimiteDePreco(
            $perguntaNormalizada
        );

        if ($limite !== null) {
            return $this->responderPorLimiteDePreco(
                $produtos,
                $limite
            );
        }

        if ($this->perguntaSobreRestricao($perguntaNormalizada)) {
            return $this->responderRestricao(
                $produtos,
                $perguntaNormalizada
            );
        }

        $encontrados = $this->buscarProdutos(
            $produtos,
            $perguntaNormalizada
        );

        if ($encontrados->isNotEmpty()) {
            return $this->responderProdutos($encontrados);
        }

        return $this->resposta(
            "🤔 Não encontrei essa informação no cardápio.\n\n" .
            "Você pode perguntar sobre:\n\n" .
            "• produtos disponíveis\n" .
            "• preços\n" .
            "• ingredientes\n" .
            "• categorias\n" .
            "• restrições alimentares\n\n" .
            "Tente escrever sua pergunta de outra forma. 😊"
        );
    }

    private function buscarProdutos(
        Collection $produtos,
        string $pergunta
    ): Collection {
        $palavrasIgnoradas = [
            'quero',
            'gostaria',
            'tem',
            'voces',
            'qual',
            'quais',
            'uma',
            'um',
            'algo',
            'produto',
            'produtos',
            'por',
            'favor',
            'mostrar',
            'mostre',
            'ver',
        ];

        $termos = collect(explode(' ', $pergunta))
            ->reject(
                fn($termo) =>
                mb_strlen($termo) < 3 ||
                in_array(
                    $termo,
                    $palavrasIgnoradas,
                    true
                )
            )
            ->values();

        if ($termos->isEmpty()) {
            return collect();
        }

        return $produtos
            ->map(function ($produto) use ($termos) {
                $conteudo = $this->normalizar(
                    implode(' ', [
                        $produto->nome,
                        $produto->descricao,
                        $produto->categoria_nome,
                        $produto->palavras_chave,
                        $produto->sinonimos,
                        $produto->ingredientes,
                        $produto->restricoes,
                        $produto->tags,
                    ])
                );

                $pontuacao = $termos->sum(
                    fn($termo) =>
                    str_contains($conteudo, $termo)
                    ? 1
                    : 0
                );

                $produto->pontuacao_busca = $pontuacao;

                return $produto;
            })
            ->filter(
                fn($produto) =>
                $produto->pontuacao_busca > 0
            )
            ->sortByDesc('pontuacao_busca')
            ->take(5)
            ->values();
    }

    private function responderProdutos(Collection $produtos): array
    {
        $categoria = $this->categoriaPredominante($produtos);

        $rotulo = $this->rotuloCategoria($categoria);

        $quantidade = $produtos->count();

        if ($quantidade === 1) {

            $produto = $produtos->first();

            $mensagem =
                "😊 Encontrei exatamente o que você procura.\n\n";

            $mensagem .=
                "👇 Destaquei esse produto no cardápio.\n\n";

            $mensagem .=
                "Posso contar mais detalhes sobre ele?";

        } else {

            $mensagem =
                "😊 Encontrei {$quantidade} {$rotulo} para você.\n\n";

            $mensagem .=
                "👇 Destaquei todas elas no cardápio.\n\n";

            $mensagem .=
                "Qual delas chamou mais sua atenção?";

        }

        $mensagem .= $this->sugestaoFinal($categoria);

        return $this->resposta(
            $mensagem,
            $produtos->pluck('id')->all()
        );
    }

    private function responderCategorias(
        Restaurante $restaurante
    ): array {
        $categorias = $restaurante->categorias
            ->filter(
                fn($categoria) =>
                $categoria->produtos->isNotEmpty()
            );

        if ($categorias->isEmpty()) {
            return $this->resposta(
                '😕 O cardápio ainda não possui categorias disponíveis.'
            );
        }

        $mensagem =
            "😊 Claro!\n\n" .
            "📋 Nosso cardápio possui estas categorias:\n\n";

        foreach ($categorias as $categoria) {
            $quantidade = $categoria->produtos->count();

            $textoProduto = $quantidade === 1
                ? 'produto'
                : 'produtos';

            $emoji = $this->emojiCategoria(
                $categoria->nome
            );

            $mensagem .=
                "{$emoji} {$categoria->nome}\n" .
                "   {$quantidade} {$textoProduto}\n\n";
        }

        $mensagem .=
            "👇 Qual delas você gostaria de conhecer? 😊";

        return $this->resposta(
            trim($mensagem)
        );
    }

    private function responderMenorPreco(
        Collection $produtos
    ): array {
        $produto = $produtos
            ->sortBy('preco')
            ->first();

        $preco = number_format(
            (float) $produto->preco,
            2,
            ',',
            '.'
        );

        $emoji = $this->emojiCategoria(
            $produto->categoria_nome ?? ''
        );

        $mensagem =
            "💰 Se você procura economia, encontrei esta opção:\n\n" .
            "{$emoji} {$produto->nome}\n" .
            "💵 R$ {$preco}\n";

        if (!empty($produto->descricao)) {
            $mensagem .=
                "\n{$produto->descricao}\n";
        }

        $mensagem .=
            "\n👇 Destaquei o produto no cardápio.\n\n" .
            "Posso mostrar outras opções nessa faixa de preço? 😊";

        return $this->resposta(
            $mensagem,
            [$produto->id]
        );
    }

    private function responderPorLimiteDePreco(
        Collection $produtos,
        float $limite
    ): array {

        $encontrados = $produtos
            ->filter(
                fn($produto) =>
                (float) $produto->preco <= $limite
            )
            ->sortBy('preco')
            ->take(5)
            ->values();

        $valor = number_format(
            $limite,
            2,
            ',',
            '.'
        );

        if ($encontrados->isEmpty()) {

            return $this->resposta(
                "😕 Não encontrei produtos de até R$ {$valor}.\n\nPosso mostrar opções próximas desse valor?"
            );

        }

        return $this->resposta(
            "💰 Encontrei {$encontrados->count()} opções de até R$ {$valor}.\n\n👇 Destaquei todas elas no cardápio.\n\nQual delas chamou mais sua atenção? 😊",
            $encontrados->pluck('id')->all()
        );
    }

    private function responderRestricao(
        Collection $produtos,
        string $pergunta
    ): array {
        $restricoesConhecidas = [
            'lactose',
            'gluten',
            'acucar',
            'carne',
            'cebola',
        ];

        $restricao = collect($restricoesConhecidas)
            ->first(
                fn($item) =>
                str_contains($pergunta, $item)
            );

        if (!$restricao) {
            return $this->resposta(
                "😊 Posso ajudar.\n\n" .
                "Qual ingrediente ou restrição alimentar você deseja evitar?"
            );
        }

        $seguros = $produtos
            ->filter(function ($produto) use ($restricao) {
                $restricoes = $this->normalizar(
                    $produto->restricoes ?? ''
                );

                $ingredientes = $this->normalizar(
                    $produto->ingredientes ?? ''
                );

                return !str_contains(
                    $restricoes,
                    $restricao
                ) && !str_contains(
                        $ingredientes,
                        $restricao
                    );
            })
            ->take(5)
            ->values();

        if ($seguros->isEmpty()) {
            return $this->resposta(
                "😕 Não tenho informações suficientes para indicar uma opção sem {$restricao}.\n\n" .
                "⚠️ Em caso de alergia ou intolerância, confirme diretamente com o restaurante antes de consumir."
            );
        }

        $mensagem =
            "😊 Encontrei algumas opções que não mencionam {$restricao} nas informações cadastradas:\n\n";

        foreach ($seguros as $produto) {
            $preco = number_format(
                (float) $produto->preco,
                2,
                ',',
                '.'
            );

            $emoji = $this->emojiCategoria(
                $produto->categoria_nome ?? ''
            );

            $mensagem .=
                "{$emoji} {$produto->nome}\n" .
                "💰 R$ {$preco}\n\n";
        }

        $mensagem .=
            "👇 Destaquei essas opções no cardápio.\n\n" .
            "⚠️ Em caso de alergia ou intolerância, confirme sempre com o restaurante antes de consumir.";

        return $this->resposta(
            "😊 Encontrei {$seguros->count()} opções que não mencionam {$restricao} nas informações cadastradas.\n\n👇 Destaquei todas elas no cardápio.\n\n⚠️ Em caso de alergia ou intolerância, confirme sempre com o restaurante antes de consumir.",
            $seguros->pluck('id')->all()
        );
    }

    private function perguntaSobreCategorias(
        string $pergunta
    ): bool {
        return collect([
            'categoria',
            'categorias',
            'cardapio completo',
            'o que voces tem',
            'o que tem',
            'quais categorias',
            'quais opcoes',
        ])->contains(
                fn($termo) =>
                str_contains($pergunta, $termo)
            );
    }

    private function perguntaSobreMenorPreco(
        string $pergunta
    ): bool {
        return str_contains($pergunta, 'mais barato')
            || str_contains($pergunta, 'menor preco')
            || str_contains($pergunta, 'mais em conta')
            || str_contains($pergunta, 'mais barato que tem');
    }

    private function perguntaSobreRestricao(
        string $pergunta
    ): bool {
        return str_contains($pergunta, 'sem ')
            || str_contains($pergunta, 'alerg')
            || str_contains($pergunta, 'intoler')
            || str_contains($pergunta, 'nao posso comer')
            || str_contains($pergunta, 'nao como');
    }

    private function extrairLimiteDePreco(
        string $pergunta
    ): ?float {
        if (
            !str_contains($pergunta, 'ate ')
            && !str_contains($pergunta, 'por menos de')
            && !str_contains($pergunta, 'no maximo')
            && !str_contains($pergunta, 'gastar ')
        ) {
            return null;
        }

        preg_match(
            '/(?:r\$\s*)?(\d+(?:[.,]\d{1,2})?)/',
            $pergunta,
            $resultado
        );

        if (!isset($resultado[1])) {
            return null;
        }

        return (float) str_replace(
            ',',
            '.',
            $resultado[1]
        );
    }

    private function categoriaPredominante(
        Collection $produtos
    ): string {
        return (string) $produtos
            ->groupBy(
                fn($produto) =>
                $produto->categoria_nome ?? ''
            )
            ->sortByDesc(
                fn($grupo) => $grupo->count()
            )
            ->keys()
            ->first();
    }

    private function emojiCategoria(
        ?string $categoria
    ): string {
        $categoriaNormalizada = $this->normalizar(
            $categoria
        );

        if (
            str_contains($categoriaNormalizada, 'pizza')
        ) {
            return '🍕';
        }

        if (
            str_contains($categoriaNormalizada, 'bebida') ||
            str_contains($categoriaNormalizada, 'refrigerante') ||
            str_contains($categoriaNormalizada, 'suco')
        ) {
            return '🥤';
        }

        if (
            str_contains($categoriaNormalizada, 'lanche') ||
            str_contains($categoriaNormalizada, 'hamburg')
        ) {
            return '🍔';
        }

        if (
            str_contains($categoriaNormalizada, 'sobremesa') ||
            str_contains($categoriaNormalizada, 'doce') ||
            str_contains($categoriaNormalizada, 'bolo')
        ) {
            return '🍰';
        }

        if (
            str_contains($categoriaNormalizada, 'cafe')
        ) {
            return '☕';
        }

        if (
            str_contains($categoriaNormalizada, 'acai')
        ) {
            return '🫐';
        }

        if (
            str_contains($categoriaNormalizada, 'sushi') ||
            str_contains($categoriaNormalizada, 'japones')
        ) {
            return '🍣';
        }

        return '🍽️';
    }

    private function rotuloCategoria(
        ?string $categoria
    ): string {
        $categoriaNormalizada = $this->normalizar(
            $categoria
        );

        if (
            str_contains($categoriaNormalizada, 'pizza')
        ) {
            return 'pizzas';
        }

        if (
            str_contains($categoriaNormalizada, 'bebida')
        ) {
            return 'bebidas';
        }

        if (
            str_contains($categoriaNormalizada, 'lanche') ||
            str_contains($categoriaNormalizada, 'hamburg')
        ) {
            return 'lanches';
        }

        if (
            str_contains($categoriaNormalizada, 'sobremesa') ||
            str_contains($categoriaNormalizada, 'doce')
        ) {
            return 'sobremesas';
        }

        return 'opções';
    }

    private function sugestaoFinal(
        ?string $categoria
    ): string {
        $categoriaNormalizada = $this->normalizar(
            $categoria
        );

        if (
            str_contains($categoriaNormalizada, 'pizza')
        ) {
            return "\n\n🥤 Posso sugerir uma bebida para acompanhar?";
        }

        if (
            str_contains($categoriaNormalizada, 'lanche') ||
            str_contains($categoriaNormalizada, 'hamburg')
        ) {
            return "\n\n🍟 Quer conhecer algum acompanhamento?";
        }

        if (
            str_contains($categoriaNormalizada, 'sobremesa') ||
            str_contains($categoriaNormalizada, 'doce')
        ) {
            return "\n\n☕ Posso mostrar uma bebida que combine com a sobremesa?";
        }

        if (
            str_contains($categoriaNormalizada, 'bebida')
        ) {
            return "\n\n🍽️ Quer conhecer alguma opção para acompanhar?";
        }

        return "\n\n😊 Posso ajudar você a escolher outra opção?";
    }

    private function normalizar(
        ?string $texto
    ): string {
        return Str::of($texto ?? '')
            ->lower()
            ->ascii()
            ->replaceMatches(
                '/[^\pL\pN\s.,]/u',
                ''
            )
            ->squish()
            ->toString();
    }

    private function resposta(
        string $mensagem,
        array $produtoIds = []
    ): array {
        return [
            'mensagem' => $mensagem,
            'produto_ids' => $produtoIds,
        ];
    }
}