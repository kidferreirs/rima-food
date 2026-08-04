<x-rimafood.layout>

    <div class="mx-auto max-w-6xl p-4 sm:p-6 lg:p-8">

        <div class="mb-6 sm:mb-8">

            <h1 class="text-3xl font-bold sm:text-4xl">
                Importar Cardápio
            </h1>

            <p class="mt-2 text-sm text-gray-500 sm:text-base">
                Envie o cardápio que você já utiliza.
            </p>

            <p class="mt-1 text-xs text-gray-400 sm:text-sm">
                {{ $restaurante->nome }}
            </p>

        </div>

        @if($errors->any())
            <div
                class="
                    mb-6 rounded-xl border border-red-200
                    bg-red-50 p-4 text-sm text-red-800
                "
            >
                <ul class="space-y-1">
                    @foreach($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('error'))
            <div
                class="
                    mb-6 rounded-xl border border-red-200
                    bg-red-50 p-4 text-sm text-red-800
                "
            >
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div
                class="
                    mb-6 rounded-xl border border-green-200
                    bg-green-50 p-4 text-sm text-green-800
                "
            >
                {{ session('success') }}
            </div>
        @endif

        <section class="mb-8 rounded-2xl bg-white p-5 shadow-sm sm:p-6">

            <div class="mb-5">

                <h2 class="text-xl font-bold sm:text-2xl">
                    Envie seu cardápio
                </h2>

                <p class="mt-2 text-sm text-gray-500">
                    Formatos aceitos: CSV, TXT, Excel, PDF, JPG e PNG.
                </p>

                <p class="mt-1 text-xs text-gray-400">
                    Tamanho máximo: 15 MB.
                </p>

            </div>

            <div class="mb-5 grid grid-cols-2 gap-3 text-center sm:grid-cols-4">

                <div class="rounded-xl bg-gray-50 p-3">
                    <p class="font-semibold text-gray-800">CSV</p>
                    <p class="mt-1 text-xs text-gray-500">Planilha simples</p>
                </div>

                <div class="rounded-xl bg-gray-50 p-3">
                    <p class="font-semibold text-gray-800">Excel</p>
                    <p class="mt-1 text-xs text-gray-500">XLS ou XLSX</p>
                </div>

                <div class="rounded-xl bg-gray-50 p-3">
                    <p class="font-semibold text-gray-800">PDF</p>
                    <p class="mt-1 text-xs text-gray-500">Texto ou escaneado</p>
                </div>

                <div class="rounded-xl bg-gray-50 p-3">
                    <p class="font-semibold text-gray-800">Imagem</p>
                    <p class="mt-1 text-xs text-gray-500">JPG ou PNG</p>
                </div>

            </div>

            <form
                action="{{ route(
                    'restaurante.importacao.preview',
                    $restaurante->slug
                ) }}"
                method="POST"
                enctype="multipart/form-data"
                class="space-y-4"
            >
                @csrf

                <label
                    for="arquivo"
                    class="
                        flex min-h-36 cursor-pointer
                        flex-col items-center justify-center
                        rounded-2xl border-2 border-dashed
                        border-gray-300 bg-gray-50
                        p-5 text-center transition
                        hover:border-green-400 hover:bg-green-50
                    "
                >
                    <span class="text-base font-semibold text-gray-800">
                        Escolher arquivo
                    </span>

                    <span class="mt-2 text-sm text-gray-500">
                        Toque aqui para selecionar o seu cardápio.
                    </span>

                    <span
                        id="nomeArquivoSelecionado"
                        class="
                            mt-3 hidden rounded-full bg-white
                            px-3 py-1 text-xs font-semibold
                            text-green-700 shadow-sm
                        "
                    ></span>
                </label>

                <input
                    type="file"
                    id="arquivo"
                    name="arquivo"
                    accept=".csv,.txt,.xls,.xlsx,.pdf,.jpg,.jpeg,.png"
                    required
                    class="hidden"
                >

                <button
                    type="submit"
                    class="
                        inline-flex w-full items-center
                        justify-center rounded-xl
                        bg-blue-600 px-6 py-3
                        text-sm font-bold text-white
                        transition hover:bg-blue-700 sm:w-auto
                    "
                >
                    Enviar e analisar
                </button>

            </form>

            <div class="mt-5 border-t border-gray-100 pt-5">

                <p class="text-sm text-gray-500">
                    Para CSV ou Excel, use as colunas:
                    <strong>categoria, produto, descricao, preco</strong>.
                </p>

                <a
                    href="{{ route(
                        'restaurante.importacao.modelo',
                        $restaurante->slug
                    ) }}"
                    class="
                        mt-3 inline-flex items-center
                        justify-center rounded-xl
                        border border-gray-200 px-4 py-2.5
                        text-sm font-semibold text-gray-700
                        transition hover:bg-gray-50
                    "
                >
                    Baixar modelo CSV
                </a>

            </div>

        </section>

        @if(!empty($arquivoRecebido))

            @php
                $tamanhoMb = (
                    ($arquivoRecebido['tamanho'] ?? 0)
                    / 1024
                    / 1024
                );

                $subtipoLabel = match (
                    $arquivoRecebido['subtipo'] ?? null
                ) {
                    'pdf_com_texto' => 'PDF com texto',
                    'pdf_escaneado' => 'PDF escaneado',
                    'imagem' => 'Imagem',
                    'excel' => 'Planilha Excel',
                    'csv' => 'CSV ou TXT',
                    default => strtoupper(
                        $arquivoRecebido['extensao'] ?? ''
                    ),
                };

                $extracaoConcluida =
                    ($arquivoRecebido['extracao_status'] ?? null)
                    === 'concluida';

                $metodoLabel = match (
                    $arquivoRecebido['metodo_extracao'] ?? null
                ) {
                    'pdftotext' => 'Texto interno do PDF',
                    'ocr_pdf' => 'OCR das páginas do PDF',
                    'ocr_imagem' => 'OCR da imagem',
                    'phpspreadsheet' => 'Leitor de planilha',
                    'leitor_csv' => 'Leitor CSV',
                    default => 'Não identificado',
                };
            @endphp

            <section
                class="
                    mb-8 rounded-2xl border
                    {{ $extracaoConcluida
                        ? 'border-green-200 bg-green-50'
                        : 'border-orange-200 bg-orange-50' }}
                    p-5 shadow-sm sm:p-6
                "
            >

                <div class="flex items-start justify-between gap-4">

                    <div class="min-w-0">

                        <p
                            class="
                                text-sm font-semibold
                                {{ $extracaoConcluida
                                    ? 'text-green-700'
                                    : 'text-orange-700' }}
                            "
                        >
                            {{ $extracaoConcluida
                                ? 'Arquivo analisado'
                                : 'Arquivo detectado' }}
                        </p>

                        <h2
                            class="
                                mt-1 break-words
                                text-lg font-bold text-gray-900
                            "
                        >
                            {{ $arquivoRecebido['nome_original'] }}
                        </h2>

                    </div>

                    <span
                        class="
                            shrink-0 rounded-full bg-white
                            px-3 py-1 text-xs font-bold
                            text-green-700 shadow-sm
                        "
                    >
                        {{ $subtipoLabel }}
                    </span>

                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">

                    <div class="rounded-xl bg-white/70 p-3">
                        <p class="text-xs text-gray-500">
                            Tipo real
                        </p>

                        <p class="mt-1 break-all text-sm font-semibold text-gray-900">
                            {{ $arquivoRecebido['mime_type'] }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-white/70 p-3">
                        <p class="text-xs text-gray-500">
                            Tamanho
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ number_format($tamanhoMb, 2, ',', '.') }} MB
                        </p>
                    </div>

                    <div class="rounded-xl bg-white/70 p-3">
                        <p class="text-xs text-gray-500">
                            Páginas
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $arquivoRecebido['paginas'] ?? '-' }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-white/70 p-3">
                        <p class="text-xs text-gray-500">
                            Situação
                        </p>

                        <p class="mt-1 font-semibold text-gray-900">
                            {{ $extracaoConcluida
                                ? 'Pronto para IA'
                                : 'Extração não concluída' }}
                        </p>
                    </div>

                </div>

                @if($extracaoConcluida)

                    <div class="mt-4 rounded-xl bg-white/80 p-4">

                        <p class="font-bold text-gray-900">
                            Análise concluída
                        </p>

                        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3">

                            <div>
                                <p class="text-xs text-gray-500">
                                    Método utilizado
                                </p>

                                <p class="mt-1 font-semibold text-gray-900">
                                    {{ $metodoLabel }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500">
                                    Texto extraído
                                </p>

                                <p class="mt-1 font-semibold text-gray-900">
                                    {{ number_format(
                                        $arquivoRecebido[
                                            'texto_caracteres'
                                        ] ?? 0,
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                    caracteres úteis
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500">
                                    Próxima etapa
                                </p>

                                <p class="mt-1 font-semibold text-green-700">
                                    Estruturar com IA
                                </p>
                            </div>

                        </div>

                    </div>

                @endif

            </section>

        @endif

        @if(!empty($textoPrevia))

            <section class="mb-8 rounded-2xl bg-white p-5 shadow-sm sm:p-6">

                <div class="flex flex-wrap items-start justify-between gap-4">

                    <div>
                        <h2 class="text-xl font-bold sm:text-2xl">
                            Conteúdo extraído
                        </h2>

                        <p class="mt-2 text-sm text-gray-500">
                            Prévia do texto encontrado no cardápio.
                        </p>
                    </div>

                    @if(empty($linhas))
                        <form
                            id="formOrganizarCardapio"
                            action="{{ route(
                                'restaurante.importacao.estruturar',
                                $restaurante->slug
                            ) }}"
                            method="POST"
                        >
                            @csrf

                            <button
                                id="botaoOrganizarCardapio"
                                type="submit"
                                class="
                                    inline-flex items-center justify-center
                                    rounded-xl bg-purple-600
                                    px-5 py-3 text-sm font-bold text-white
                                    transition hover:bg-purple-700
                                    disabled:cursor-wait disabled:opacity-70
                                "
                            >
                                Organizar Cardápio
                            </button>
                        </form>
                    @else
                        <span
                            class="
                                rounded-full bg-green-100
                                px-4 py-2 text-sm font-semibold text-green-700
                            "
                        >
                            Cardápio organizado
                        </span>
                    @endif

                </div>

                <pre
                    class="
                        mt-5 max-h-[520px] overflow-auto
                        whitespace-pre-wrap rounded-2xl
                        border border-gray-200 bg-gray-50
                        p-4 text-sm leading-6 text-gray-700
                    "
                >{{ $textoPrevia }}</pre>

                <p class="mt-3 text-xs text-gray-400">
                    Clique em Organizar Cardápio para transformar este texto
                    em categorias, produtos, descrições e preços.
                </p>

            </section>

        @endif

        @if(!empty($linhas))

            <section class="rounded-2xl bg-white p-5 shadow-sm sm:p-6">

                <div class="flex flex-wrap items-start justify-between gap-4">

                    <div>
                        <h2 class="text-xl font-bold sm:text-2xl">
                            Pré-visualização
                        </h2>

                        <p class="mt-2 text-sm text-gray-500">
                            Confira os dados antes de confirmar a importação.
                        </p>
                    </div>

                    @if(
                        ($arquivoRecebido['enriquecimento_status'] ?? null)
                        !== 'concluido'
                    )
                        <form
                            id="formMelhorarCardapio"
                            action="{{ route(
                                'restaurante.importacao.enriquecer',
                                $restaurante->slug
                            ) }}"
                            method="POST"
                        >
                            @csrf

                            <button
                                id="botaoMelhorarCardapio"
                                type="submit"
                                class="
                                    inline-flex items-center justify-center
                                    rounded-xl bg-purple-600
                                    px-5 py-3 text-sm font-bold text-white
                                    transition hover:bg-purple-700
                                    disabled:cursor-wait disabled:opacity-70
                                "
                            >
                                Melhorar com IA
                            </button>
                        </form>
                    @else
                        @if(
                            ($arquivoRecebido['analise_status'] ?? null)
                            !== 'concluida'
                        )
                            <form
                                id="formAnalisarCardapio"
                                action="{{ route(
                                    'restaurante.importacao.analisar',
                                    $restaurante->slug
                                ) }}"
                                method="POST"
                            >
                                @csrf

                                <button
                                    id="botaoAnalisarCardapio"
                                    type="submit"
                                    class="
                                        inline-flex items-center justify-center
                                        rounded-xl bg-indigo-600
                                        px-5 py-3 text-sm font-bold text-white
                                        transition hover:bg-indigo-700
                                        disabled:cursor-wait disabled:opacity-70
                                    "
                                >
                                    Analisar configurações
                                </button>
                            </form>
                        @else
                            <span
                                class="
                                    rounded-full bg-indigo-100
                                    px-4 py-2 text-sm font-semibold text-indigo-700
                                "
                            >
                                Análise concluída
                            </span>
                        @endif
                    @endif

                </div>

                @if(!empty($estatisticas))

                    <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-5">

                        <div class="rounded-xl bg-gray-50 p-4">
                            <p class="text-xs text-gray-500">Categorias</p>
                            <p class="mt-1 text-2xl font-bold">
                                {{ $estatisticas['categorias_total'] }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4">
                            <p class="text-xs text-gray-500">Novas categorias</p>
                            <p class="mt-1 text-2xl font-bold">
                                {{ $estatisticas['categorias_novas'] }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4">
                            <p class="text-xs text-gray-500">Produtos</p>
                            <p class="mt-1 text-2xl font-bold">
                                {{ $estatisticas['produtos_total'] }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4">
                            <p class="text-xs text-gray-500">Novos</p>
                            <p class="mt-1 text-2xl font-bold">
                                {{ $estatisticas['produtos_novos'] }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-gray-50 p-4">
                            <p class="text-xs text-gray-500">Atualizados</p>
                            <p class="mt-1 text-2xl font-bold">
                                {{ $estatisticas['produtos_atualizados'] }}
                            </p>
                        </div>

                    </div>

                @endif

                <div class="mt-6 space-y-3 md:hidden">

                    @foreach($linhas as $linha)

                        <article class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">

                            <p class="text-xs font-semibold text-green-700">
                                {{ $linha['categoria'] }}
                            </p>

                            <h3 class="mt-1 font-bold text-gray-900">
                                {{ $linha['nome'] }}
                            </h3>

                            @if($linha['descricao'])
                                <p class="mt-2 text-sm text-gray-500">
                                    {{ $linha['descricao'] }}
                                </p>
                            @endif

                            <p class="mt-3 text-lg font-bold text-gray-900">
                                R$ {{ number_format(
                                    $linha['preco'],
                                    2,
                                    ',',
                                    '.'
                                ) }}
                            </p>

                            @if(!empty($linha['palavras_chave']))
                                <div class="mt-4">
                                    <p class="text-xs font-semibold text-gray-500">
                                        Palavras-chave
                                    </p>

                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach($linha['palavras_chave'] as $item)
                                            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs text-blue-700">
                                                {{ $item }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(!empty($linha['sinonimos']))
                                <p class="mt-3 text-xs text-gray-500">
                                    <strong>Sinônimos:</strong>
                                    {{ implode(', ', $linha['sinonimos']) }}
                                </p>
                            @endif

                            @if(!empty($linha['ingredientes']))
                                <p class="mt-2 text-xs text-gray-500">
                                    <strong>Ingredientes:</strong>
                                    {{ implode(', ', $linha['ingredientes']) }}
                                </p>
                            @endif

                            @if(!empty($linha['restricoes']))
                                <p class="mt-2 text-xs text-orange-700">
                                    <strong>Restrições:</strong>
                                    {{ implode(', ', $linha['restricoes']) }}
                                </p>
                            @endif

                            @if(!empty($linha['tags']))
                                <p class="mt-2 text-xs text-purple-700">
                                    <strong>Tags:</strong>
                                    {{ implode(', ', $linha['tags']) }}
                                </p>
                            @endif

                            @if(!empty($linha['grupos_sugeridos']))
                                <div class="mt-4 border-t border-gray-100 pt-4">
                                    <p class="text-xs font-bold text-indigo-700">
                                        Grupos sugeridos
                                    </p>

                                    <div class="mt-2 space-y-2">
                                        @foreach(
                                            $linha['grupos_sugeridos']
                                            as $grupo
                                        )
                                            <div class="rounded-xl bg-indigo-50 p-3">
                                                <p class="text-sm font-semibold text-gray-900">
                                                    {{ $grupo['nome'] }}
                                                </p>

                                                <p class="mt-1 text-xs text-gray-500">
                                                    {{ $grupo['tipo'] }}
                                                    · mínimo {{ $grupo['minimo'] }}
                                                    · máximo {{ $grupo['maximo'] }}
                                                </p>

                                                @if(!empty($grupo['opcoes']))
                                                    <p class="mt-2 text-xs text-indigo-700">
                                                        {{ collect(
                                                            $grupo['opcoes']
                                                        )->pluck('nome')->implode(', ') }}
                                                    </p>
                                                @endif

                                                <p class="mt-2 text-xs text-gray-500">
                                                    {{ $grupo['justificativa'] }}
                                                </p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                        </article>

                    @endforeach

                </div>

                <div class="mt-6 hidden overflow-x-auto md:block">

                    <table class="w-full">

                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="p-3 text-left">Categoria</th>
                                <th class="p-3 text-left">Produto</th>
                                <th class="p-3 text-left">Descrição</th>
                                <th class="p-3 text-left">Preço</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($linhas as $linha)

                                <tr class="border-b">

                                    <td class="p-3">
                                        {{ $linha['categoria'] }}
                                    </td>

                                    <td class="p-3 font-bold">
                                        {{ $linha['nome'] }}
                                    </td>

                                    <td class="p-3 text-gray-500">

                                        <p>{{ $linha['descricao'] }}</p>

                                        @if(!empty($linha['palavras_chave']))
                                            <p class="mt-2 text-xs text-blue-700">
                                                <strong>Palavras-chave:</strong>
                                                {{ implode(
                                                    ', ',
                                                    $linha['palavras_chave']
                                                ) }}
                                            </p>
                                        @endif

                                        @if(!empty($linha['sinonimos']))
                                            <p class="mt-1 text-xs text-gray-500">
                                                <strong>Sinônimos:</strong>
                                                {{ implode(
                                                    ', ',
                                                    $linha['sinonimos']
                                                ) }}
                                            </p>
                                        @endif

                                        @if(!empty($linha['ingredientes']))
                                            <p class="mt-1 text-xs text-green-700">
                                                <strong>Ingredientes:</strong>
                                                {{ implode(
                                                    ', ',
                                                    $linha['ingredientes']
                                                ) }}
                                            </p>
                                        @endif

                                        @if(!empty($linha['restricoes']))
                                            <p class="mt-1 text-xs text-orange-700">
                                                <strong>Restrições:</strong>
                                                {{ implode(
                                                    ', ',
                                                    $linha['restricoes']
                                                ) }}
                                            </p>
                                        @endif

                                        @if(!empty($linha['tags']))
                                            <p class="mt-1 text-xs text-purple-700">
                                                <strong>Tags:</strong>
                                                {{ implode(
                                                    ', ',
                                                    $linha['tags']
                                                ) }}
                                            </p>
                                        @endif

                                        @if(!empty($linha['grupos_sugeridos']))
                                            <div class="mt-3 rounded-lg bg-indigo-50 p-3">
                                                <p class="text-xs font-bold text-indigo-700">
                                                    Grupos sugeridos
                                                </p>

                                                @foreach(
                                                    $linha['grupos_sugeridos']
                                                    as $grupo
                                                )
                                                    <div class="mt-2">
                                                        <p class="text-xs font-semibold text-gray-900">
                                                            {{ $grupo['nome'] }}
                                                            <span class="font-normal text-gray-500">
                                                                ({{ $grupo['tipo'] }})
                                                            </span>
                                                        </p>

                                                        @if(!empty($grupo['opcoes']))
                                                            <p class="text-xs text-indigo-700">
                                                                {{ collect(
                                                                    $grupo['opcoes']
                                                                )->pluck('nome')->implode(', ') }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                    </td>

                                    <td class="p-3 whitespace-nowrap">
                                        R$ {{ number_format(
                                            $linha['preco'],
                                            2,
                                            ',',
                                            '.'
                                        ) }}
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

                @if(!empty($auditoria))

                    <div class="mt-8 rounded-2xl border border-indigo-200 bg-indigo-50 p-5">

                        <div class="flex flex-wrap items-start justify-between gap-4">

                            <div>
                                <h3 class="text-xl font-bold text-gray-900">
                                    Auditoria do cardápio
                                </h3>

                                <p class="mt-2 text-sm text-gray-600">
                                    {{ $auditoria['resumo'] ?? '' }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-white px-5 py-3 text-center shadow-sm">
                                <p class="text-xs text-gray-500">
                                    Pontuação
                                </p>

                                <p class="mt-1 text-3xl font-bold text-indigo-700">
                                    {{ $auditoria['pontuacao'] ?? 0 }}
                                </p>
                            </div>

                        </div>

                        @if(!empty($auditoria['recomendacoes']))

                            <div class="mt-5 grid gap-3 md:grid-cols-2">

                                @foreach(
                                    $auditoria['recomendacoes']
                                    as $recomendacao
                                )
                                    @php
                                        $prioridadeClasse = match(
                                            $recomendacao['prioridade']
                                            ?? 'baixa'
                                        ) {
                                            'alta' =>
                                                'bg-red-100 text-red-700',
                                            'media' =>
                                                'bg-orange-100 text-orange-700',
                                            default =>
                                                'bg-blue-100 text-blue-700',
                                        };
                                    @endphp

                                    <article class="rounded-xl bg-white p-4 shadow-sm">

                                        <div class="flex items-start justify-between gap-3">

                                            <h4 class="font-bold text-gray-900">
                                                {{ $recomendacao['titulo'] }}
                                            </h4>

                                            <span
                                                class="
                                                    rounded-full px-2.5 py-1
                                                    text-xs font-semibold
                                                    {{ $prioridadeClasse }}
                                                "
                                            >
                                                {{ ucfirst(
                                                    $recomendacao[
                                                        'prioridade'
                                                    ]
                                                    ?? 'baixa'
                                                ) }}
                                            </span>

                                        </div>

                                        <p class="mt-2 text-sm text-gray-600">
                                            {{ $recomendacao['descricao'] }}
                                        </p>

                                        @if(
                                            !empty(
                                                $recomendacao[
                                                    'produtos_afetados'
                                                ]
                                            )
                                        )
                                            <p class="mt-3 text-xs text-gray-500">
                                                <strong>Produtos:</strong>
                                                {{ implode(
                                                    ', ',
                                                    $recomendacao[
                                                        'produtos_afetados'
                                                    ]
                                                ) }}
                                            </p>
                                        @endif

                                    </article>
                                @endforeach

                            </div>

                        @endif

                    </div>

                @endif

                <form
                    action="{{ route(
                        'restaurante.importacao.importar',
                        $restaurante->slug
                    ) }}"
                    method="POST"
                    class="mt-6"
                >
                    @csrf

                    <button
                        type="submit"
                        class="
                            inline-flex w-full items-center
                            justify-center rounded-xl
                            bg-green-600 px-8 py-3
                            text-sm font-bold text-white
                            transition hover:bg-green-700 sm:w-auto
                        "
                    >
                        Confirmar importação
                    </button>
                </form>

            </section>

        @endif

    </div>

    <script>
        const inputArquivo = document.getElementById('arquivo');
        const nomeSelecionado = document.getElementById(
            'nomeArquivoSelecionado'
        );

        inputArquivo?.addEventListener('change', function () {
            const arquivo = this.files?.[0];

            if (!arquivo) {
                nomeSelecionado.textContent = '';
                nomeSelecionado.classList.add('hidden');
                return;
            }

            nomeSelecionado.textContent = arquivo.name;
            nomeSelecionado.classList.remove('hidden');
        });

        const formOrganizar = document.getElementById(
            'formOrganizarCardapio'
        );
        const botaoOrganizar = document.getElementById(
            'botaoOrganizarCardapio'
        );

        formOrganizar?.addEventListener('submit', function () {
            if (!botaoOrganizar) {
                return;
            }

            botaoOrganizar.disabled = true;
            botaoOrganizar.textContent =
                'Organizando o cardápio...';
        });

        const formMelhorar = document.getElementById(
            'formMelhorarCardapio'
        );
        const botaoMelhorar = document.getElementById(
            'botaoMelhorarCardapio'
        );

        formMelhorar?.addEventListener('submit', function () {
            if (!botaoMelhorar) {
                return;
            }

            botaoMelhorar.disabled = true;
            botaoMelhorar.textContent =
                'Melhorando o cardápio...';
        });

        const formAnalisar = document.getElementById(
            'formAnalisarCardapio'
        );
        const botaoAnalisar = document.getElementById(
            'botaoAnalisarCardapio'
        );

        formAnalisar?.addEventListener('submit', function () {
            if (!botaoAnalisar) {
                return;
            }

            botaoAnalisar.disabled = true;
            botaoAnalisar.textContent =
                'Analisando configurações...';
        });
    </script>

</x-rimafood.layout>