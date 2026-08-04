<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Produto;
use App\Services\AI\Importacao\CardapioStructureService;
use App\Services\AI\Importacao\CardapioEnrichmentService;
use App\Services\AI\Importacao\CardapioAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Process\Process;
use Throwable;

class CardapioImportController extends BaseRestaurantController
{
    public function index()
    {
        $restaurante = $this->restaurante();
        $arquivoRecebido = session('importacao_arquivo');

        $textoPrevia = null;

        if (
            is_array($arquivoRecebido)
            && !empty($arquivoRecebido['texto_caminho'])
            && Storage::disk('local')->exists(
                $arquivoRecebido['texto_caminho']
            )
        ) {
            $texto = Storage::disk('local')->get(
                $arquivoRecebido['texto_caminho']
            );

            $textoPrevia = Str::limit(
                trim($texto),
                4000,
                "\n\n[...]"
            );
        }

        return view('importacao.cardapio', [
            'restaurante' => $restaurante,
            'linhas' => session('importacao_cardapio', []),
            'estatisticas' => session('importacao_estatisticas', []),
            'arquivoRecebido' => $arquivoRecebido,
            'textoPrevia' => $textoPrevia,
            'auditoria' => session('importacao_auditoria'),
        ]);
    }

    public function preview(Request $request)
    {
        $restaurante = $this->restaurante();

        $request->validate([
            'arquivo' => [
                'required',
                'file',
                'mimes:csv,txt,xls,xlsx,pdf,jpg,jpeg,png',
                'max:15360',
            ],
        ], [
            'arquivo.required' => 'Selecione um arquivo para continuar.',
            'arquivo.file' => 'O arquivo enviado é inválido.',
            'arquivo.mimes' => 'Formato não suportado. Envie CSV, TXT, Excel, PDF, JPG ou PNG.',
            'arquivo.max' => 'O arquivo deve ter no máximo 15 MB.',
        ]);

        $arquivoUpload = $request->file('arquivo');

        $extensao = strtolower(
            $arquivoUpload->getClientOriginalExtension()
        );

        $mimeReal = strtolower(
            $arquivoUpload->getMimeType()
            ?: 'application/octet-stream'
        );

        $tipoDetectado = $this->detectarTipoReal(
            $extensao,
            $mimeReal
        );

        if ($tipoDetectado === null) {
            return back()->with(
                'error',
                'O conteúdo real do arquivo não corresponde a um formato suportado.'
            );
        }

        $this->removerArquivoTemporarioAnterior();

        $nomeInterno = Str::uuid()->toString() . '.' . $extensao;

        $caminho = $arquivoUpload->storeAs(
            'importacoes/cardapios/' . $restaurante->id,
            $nomeInterno,
            'local'
        );

        $caminhoCompleto = Storage::disk('local')->path($caminho);

        $detalhes = [
            'subtipo' => $tipoDetectado['subtipo'],
            'possui_texto' => null,
            'texto_extraido_caracteres' => 0,
            'paginas' => null,
            'pdf_criptografado' => null,
        ];

        if ($tipoDetectado['tipo'] === 'pdf') {
            $detalhes = array_merge(
                $detalhes,
                $this->analisarPdf($caminhoCompleto)
            );
        }

        $arquivoRecebido = [
            'nome_original' => $arquivoUpload->getClientOriginalName(),
            'nome_interno' => $nomeInterno,
            'extensao' => $extensao,
            'mime_type' => $mimeReal,
            'tamanho' => (int) $arquivoUpload->getSize(),
            'tipo' => $tipoDetectado['tipo'],
            'subtipo' => $detalhes['subtipo'],
            'possui_texto' => $detalhes['possui_texto'],
            'texto_extraido_caracteres' =>
                $detalhes['texto_extraido_caracteres'],
            'paginas' => $detalhes['paginas'],
            'pdf_criptografado' => $detalhes['pdf_criptografado'],
            'caminho' => $caminho,
            'status' => 'detectado',
            'extracao_status' => 'pendente',
            'metodo_extracao' => null,
            'texto_caminho' => null,
            'texto_caracteres' => 0,
        ];

        /*
        |--------------------------------------------------------------------------
        | CSV e Excel
        |--------------------------------------------------------------------------
        */
        if (in_array(
            $tipoDetectado['tipo'],
            ['csv', 'planilha'],
            true
        )) {
            $linhas = $this->lerPlanilhaOuCsv(
                $caminhoCompleto,
                $extensao
            );

            $estatisticas = $this->calcularEstatisticas(
                $linhas,
                $restaurante->id
            );

            $arquivoRecebido['status'] = 'processado';
            $arquivoRecebido['extracao_status'] = 'concluida';
            $arquivoRecebido['metodo_extracao'] =
                $tipoDetectado['tipo'] === 'csv'
                    ? 'leitor_csv'
                    : 'phpspreadsheet';

            session([
                'importacao_arquivo' => $arquivoRecebido,
                'importacao_cardapio' => $linhas,
                'importacao_estatisticas' => $estatisticas,
            ]);

            return redirect()
                ->route(
                    'restaurante.importacao.cardapio',
                    $restaurante->slug
                )
                ->with(
                    'success',
                    'Arquivo detectado, processado e pronto para revisão.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | PDF e imagem
        |--------------------------------------------------------------------------
        */
        if (
            $tipoDetectado['tipo'] === 'pdf'
            && ($arquivoRecebido['pdf_criptografado'] ?? false)
        ) {
            Storage::disk('local')->delete($caminho);

            return back()->with(
                'error',
                'O PDF está protegido por senha. Envie uma versão sem criptografia.'
            );
        }

        $resultadoExtracao = $this->extrairConteudo(
            $arquivoRecebido,
            $restaurante->id
        );

        $arquivoRecebido = array_merge(
            $arquivoRecebido,
            $resultadoExtracao
        );

        session([
            'importacao_arquivo' => $arquivoRecebido,
        ]);

        session()->forget([
            'importacao_cardapio',
            'importacao_estatisticas',
            'importacao_auditoria',
        ]);

        $mensagem = match ($arquivoRecebido['metodo_extracao']) {
            'pdftotext' =>
                'PDF analisado e texto interno extraído com sucesso.',
            'ocr_pdf' =>
                'PDF escaneado analisado por OCR com sucesso.',
            'ocr_imagem' =>
                'Imagem analisada por OCR com sucesso.',
            default =>
                'Arquivo analisado, mas não foi possível extrair texto útil.',
        };

        return redirect()
            ->route(
                'restaurante.importacao.cardapio',
                $restaurante->slug
            )
            ->with('success', $mensagem);
    }


    public function estruturar(
        CardapioStructureService $service
    ) {
        $restaurante = $this->restaurante();
        $arquivoRecebido = session('importacao_arquivo');

        if (!is_array($arquivoRecebido)) {
            return back()->with(
                'error',
                'Envie e analise um cardápio antes de organizar.'
            );
        }

        if (
            ($arquivoRecebido['extracao_status'] ?? null)
            !== 'concluida'
        ) {
            return back()->with(
                'error',
                'A extração do conteúdo ainda não foi concluída.'
            );
        }

        $textoCaminho = $arquivoRecebido['texto_caminho'] ?? null;

        if (
            !$textoCaminho
            || !Storage::disk('local')->exists($textoCaminho)
        ) {
            return back()->with(
                'error',
                'O texto extraído não foi encontrado. Envie o arquivo novamente.'
            );
        }

        try {
            $texto = Storage::disk('local')->get($textoCaminho);

            $linhas = $service->estruturar($texto);

            $estatisticas = $this->calcularEstatisticas(
                $linhas,
                $restaurante->id
            );

            $arquivoRecebido['status'] = 'estruturado';
            $arquivoRecebido['ia_status'] = 'concluida';
            $arquivoRecebido['categorias_ia'] = collect($linhas)
                ->pluck('categoria')
                ->filter()
                ->unique()
                ->count();
            $arquivoRecebido['produtos_ia'] = collect($linhas)
                ->pluck('nome')
                ->filter()
                ->count();

            session([
                'importacao_arquivo' => $arquivoRecebido,
                'importacao_cardapio' => $linhas,
                'importacao_estatisticas' => $estatisticas,
            ]);

            return redirect()
                ->route(
                    'restaurante.importacao.cardapio',
                    $restaurante->slug
                )
                ->with(
                    'success',
                    'Cardápio organizado com sucesso! Revise os produtos antes de importar.'
                );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                $exception->getMessage()
                ?: 'Não foi possível organizar o cardápio.'
            );
        }
    }


    public function enriquecer(
        CardapioEnrichmentService $service
    ) {
        $restaurante = $this->restaurante();
        $linhas = session('importacao_cardapio', []);
        $arquivoRecebido = session('importacao_arquivo', []);

        if (empty($linhas)) {
            return back()->with(
                'error',
                'Organize o cardápio antes de melhorá-lo com IA.'
            );
        }

        try {
            $linhasEnriquecidas = $service->enriquecer($linhas);

            if (is_array($arquivoRecebido)) {
                $arquivoRecebido['enriquecimento_status'] =
                    'concluido';
                $arquivoRecebido['produtos_enriquecidos'] =
                    count($linhasEnriquecidas);
            }

            session([
                'importacao_cardapio' => $linhasEnriquecidas,
                'importacao_arquivo' => $arquivoRecebido,
            ]);

            session()->forget('importacao_auditoria');

            return redirect()
                ->route(
                    'restaurante.importacao.cardapio',
                    $restaurante->slug
                )
                ->with(
                    'success',
                    'Cardápio melhorado com IA! Revise os dados antes de importar.'
                );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                $exception->getMessage()
                ?: 'Não foi possível melhorar o cardápio.'
            );
        }
    }


    public function analisarInteligencia(
        CardapioAnalysisService $service
    ) {
        $restaurante = $this->restaurante();
        $linhas = session('importacao_cardapio', []);
        $arquivoRecebido = session('importacao_arquivo', []);

        if (empty($linhas)) {
            return back()->with(
                'error',
                'Organize o cardápio antes de analisar as configurações.'
            );
        }

        if (
            ($arquivoRecebido['enriquecimento_status'] ?? null)
            !== 'concluido'
        ) {
            return back()->with(
                'error',
                'Melhore o cardápio com IA antes da análise final.'
            );
        }

        try {
            $resultado = $service->analisar($linhas);

            $linhasAnalisadas = $resultado['produtos'];
            $auditoria = $resultado['auditoria'];

            if (is_array($arquivoRecebido)) {
                $arquivoRecebido['analise_status'] =
                    'concluida';
                $arquivoRecebido['grupos_sugeridos'] =
                    collect($linhasAnalisadas)
                        ->sum(
                            fn($linha) => count(
                                $linha['grupos_sugeridos'] ?? []
                            )
                        );
            }

            session([
                'importacao_cardapio' => $linhasAnalisadas,
                'importacao_auditoria' => $auditoria,
                'importacao_arquivo' => $arquivoRecebido,
            ]);

            return redirect()
                ->route(
                    'restaurante.importacao.cardapio',
                    $restaurante->slug
                )
                ->with(
                    'success',
                    'Sugestões de configuração e auditoria concluídas!'
                );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                $exception->getMessage()
                ?: 'Não foi possível concluir a análise.'
            );
        }
    }

    public function importar()
    {
        $restaurante = $this->restaurante();

        $linhas = session('importacao_cardapio', []);

        if (empty($linhas)) {
            return back()->with(
                'error',
                'Nenhum item processado para importar.'
            );
        }

        $categoriasCriadas = 0;
        $produtosCriados = 0;
        $produtosAtualizados = 0;

        DB::transaction(function () use (
            $linhas,
            $restaurante,
            &$categoriasCriadas,
            &$produtosCriados,
            &$produtosAtualizados
        ) {
            foreach ($linhas as $linha) {
                if (empty($linha['nome'])) {
                    continue;
                }

                $categoriaNome = $linha['categoria']
                    ?: 'Sem categoria';

                $categoria = Categoria::where(
                    'restaurante_id',
                    $restaurante->id
                )
                    ->where('nome', $categoriaNome)
                    ->first();

                if (!$categoria) {
                    $categoria = Categoria::create([
                        'restaurante_id' => $restaurante->id,
                        'nome' => $categoriaNome,
                        'ativo' => true,
                    ]);

                    $categoriasCriadas++;
                }

                $produto = Produto::where(
                    'categoria_id',
                    $categoria->id
                )
                    ->where('nome', $linha['nome'])
                    ->first();

                if ($produto) {
                    $produto->update([
                        'descricao' => $linha['descricao'],
                        'preco' => $linha['preco'],
                        'palavras_chave' =>
                            $this->listaParaTexto(
                                $linha['palavras_chave'] ?? []
                            ),
                        'sinonimos' =>
                            $this->listaParaTexto(
                                $linha['sinonimos'] ?? []
                            ),
                        'ingredientes' =>
                            $this->listaParaTexto(
                                $linha['ingredientes'] ?? []
                            ),
                        'restricoes' =>
                            $this->listaParaTexto(
                                $linha['restricoes'] ?? []
                            ),
                        'tags' =>
                            $this->listaParaTexto(
                                $linha['tags'] ?? []
                            ),
                        'ativo' => true,
                    ]);

                    $produtosAtualizados++;
                } else {
                    Produto::create([
                        'categoria_id' => $categoria->id,
                        'nome' => $linha['nome'],
                        'descricao' => $linha['descricao'],
                        'preco' => $linha['preco'],
                        'palavras_chave' =>
                            $this->listaParaTexto(
                                $linha['palavras_chave'] ?? []
                            ),
                        'sinonimos' =>
                            $this->listaParaTexto(
                                $linha['sinonimos'] ?? []
                            ),
                        'ingredientes' =>
                            $this->listaParaTexto(
                                $linha['ingredientes'] ?? []
                            ),
                        'restricoes' =>
                            $this->listaParaTexto(
                                $linha['restricoes'] ?? []
                            ),
                        'tags' =>
                            $this->listaParaTexto(
                                $linha['tags'] ?? []
                            ),
                        'ativo' => true,
                    ]);

                    $produtosCriados++;
                }
            }
        });

        $this->removerArquivoTemporarioAnterior();

        session()->forget([
            'importacao_cardapio',
            'importacao_estatisticas',
            'importacao_arquivo',
            'importacao_auditoria',
        ]);

        return redirect()
            ->route(
                'restaurante.importacao.cardapio',
                $restaurante->slug
            )
            ->with(
                'success',
                "Importação concluída! Categorias criadas: {$categoriasCriadas}. Produtos criados: {$produtosCriados}. Produtos atualizados: {$produtosAtualizados}."
            );
    }

    public function modelo()
    {
        $conteudo = "categoria;produto;descricao;preco\n";
        $conteudo .= "Bebidas;Coca Cola 350ml;Lata bem gelada;5,00\n";
        $conteudo .= "Hambúrgueres;X Burguer;Pão, carne e queijo;24,90\n";

        return response($conteudo)
            ->header(
                'Content-Type',
                'text/csv; charset=UTF-8'
            )
            ->header(
                'Content-Disposition',
                'attachment; filename="modelo-cardapio.csv"'
            );
    }

    private function detectarTipoReal(
        string $extensao,
        string $mime
    ): ?array {
        $mimesPdf = [
            'application/pdf',
            'application/x-pdf',
        ];

        $mimesImagem = [
            'image/jpeg',
            'image/png',
        ];

        $mimesCsv = [
            'text/plain',
            'text/csv',
            'application/csv',
            'application/vnd.ms-excel',
        ];

        $mimesExcel = [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip',
        ];

        if (
            $extensao === 'pdf'
            && in_array($mime, $mimesPdf, true)
        ) {
            return [
                'tipo' => 'pdf',
                'subtipo' => 'pdf_pendente',
            ];
        }

        if (
            in_array($extensao, ['jpg', 'jpeg', 'png'], true)
            && in_array($mime, $mimesImagem, true)
        ) {
            return [
                'tipo' => 'imagem',
                'subtipo' => 'imagem',
            ];
        }

        if (
            in_array($extensao, ['csv', 'txt'], true)
            && in_array($mime, $mimesCsv, true)
        ) {
            return [
                'tipo' => 'csv',
                'subtipo' => 'csv',
            ];
        }

        if (
            in_array($extensao, ['xls', 'xlsx'], true)
            && in_array($mime, $mimesExcel, true)
        ) {
            return [
                'tipo' => 'planilha',
                'subtipo' => 'excel',
            ];
        }

        return null;
    }

    private function analisarPdf(string $caminhoCompleto): array
    {
        $resultado = [
            'subtipo' => 'pdf_escaneado',
            'possui_texto' => false,
            'texto_extraido_caracteres' => 0,
            'paginas' => null,
            'pdf_criptografado' => null,
        ];

        try {
            $processoInfo = new Process([
                '/usr/bin/pdfinfo',
                $caminhoCompleto,
            ]);

            $processoInfo->setTimeout(20);
            $processoInfo->run();

            if ($processoInfo->isSuccessful()) {
                $info = $processoInfo->getOutput();

                if (
                    preg_match(
                        '/^Pages:\s+(\d+)/mi',
                        $info,
                        $matches
                    )
                ) {
                    $resultado['paginas'] = (int) $matches[1];
                }

                if (
                    preg_match(
                        '/^Encrypted:\s+(yes|no)/mi',
                        $info,
                        $matches
                    )
                ) {
                    $resultado['pdf_criptografado'] =
                        strtolower($matches[1]) === 'yes';
                }
            }

            $processoTexto = new Process([
                '/usr/bin/pdftotext',
                '-layout',
                $caminhoCompleto,
                '-',
            ]);

            $processoTexto->setTimeout(30);
            $processoTexto->run();

            if (!$processoTexto->isSuccessful()) {
                return $resultado;
            }

            $texto = $processoTexto->getOutput();
            $quantidadeCaracteres = $this->contarCaracteresUteis(
                $texto
            );

            $possuiTexto = $quantidadeCaracteres >= 80;

            $resultado['possui_texto'] = $possuiTexto;
            $resultado['texto_extraido_caracteres'] =
                $quantidadeCaracteres;
            $resultado['subtipo'] = $possuiTexto
                ? 'pdf_com_texto'
                : 'pdf_escaneado';

            return $resultado;
        } catch (Throwable) {
            return $resultado;
        }
    }

    private function extrairConteudo(
        array $arquivo,
        int $restauranteId
    ): array {
        $resultado = [
            'status' => 'detectado',
            'extracao_status' => 'falhou',
            'metodo_extracao' => null,
            'texto_caminho' => null,
            'texto_caracteres' => 0,
        ];

        $caminhoCompleto = Storage::disk('local')->path(
            $arquivo['caminho']
        );

        try {
            $texto = '';
            $metodo = null;

            if (($arquivo['subtipo'] ?? null) === 'pdf_com_texto') {
                $texto = $this->extrairTextoPdf(
                    $caminhoCompleto
                );
                $metodo = 'pdftotext';
            } elseif (
                ($arquivo['subtipo'] ?? null) === 'pdf_escaneado'
            ) {
                $texto = $this->extrairTextoPdfPorOcr(
                    $caminhoCompleto,
                    (int) ($arquivo['paginas'] ?? 1)
                );
                $metodo = 'ocr_pdf';
            } elseif (($arquivo['tipo'] ?? null) === 'imagem') {
                $texto = $this->extrairTextoImagemPorOcr(
                    $caminhoCompleto
                );
                $metodo = 'ocr_imagem';
            }

            $texto = $this->normalizarTextoExtraido($texto);
            $caracteres = $this->contarCaracteresUteis($texto);

            if ($caracteres < 10) {
                return $resultado;
            }

            $nomeTexto = Str::uuid()->toString() . '.txt';
            $textoCaminho =
                'importacoes/textos/'
                . $restauranteId
                . '/'
                . $nomeTexto;

            Storage::disk('local')->put(
                $textoCaminho,
                $texto
            );

            return [
                'status' => 'extraido',
                'extracao_status' => 'concluida',
                'metodo_extracao' => $metodo,
                'texto_caminho' => $textoCaminho,
                'texto_caracteres' => $caracteres,
            ];
        } catch (Throwable) {
            return $resultado;
        }
    }

    private function extrairTextoPdf(
        string $caminhoCompleto
    ): string {
        $processo = new Process([
            '/usr/bin/pdftotext',
            '-layout',
            '-enc',
            'UTF-8',
            $caminhoCompleto,
            '-',
        ]);

        $processo->setTimeout(60);
        $processo->mustRun();

        return $processo->getOutput();
    }

    private function extrairTextoPdfPorOcr(
        string $caminhoCompleto,
        int $paginas
    ): string {
        $limitePaginas = min(max($paginas, 1), 20);

        $diretorioTemporario =
            storage_path(
                'app/private/importacoes/ocr-temp/'
                . Str::uuid()->toString()
            );

        if (!is_dir($diretorioTemporario)) {
            mkdir($diretorioTemporario, 0755, true);
        }

        try {
            $prefixo = $diretorioTemporario . '/pagina';

            $converter = new Process([
                '/usr/bin/pdftoppm',
                '-f',
                '1',
                '-l',
                (string) $limitePaginas,
                '-png',
                '-r',
                '200',
                $caminhoCompleto,
                $prefixo,
            ]);

            $converter->setTimeout(180);
            $converter->mustRun();

            $imagens = glob(
                $diretorioTemporario . '/pagina-*.png'
            ) ?: [];

            natsort($imagens);

            $partes = [];

            foreach ($imagens as $indice => $imagem) {
                $textoPagina = $this->executarTesseract(
                    $imagem
                );

                if (trim($textoPagina) !== '') {
                    $partes[] =
                        '--- PÁGINA '
                        . ($indice + 1)
                        . " ---\n"
                        . $textoPagina;
                }
            }

            return implode("\n\n", $partes);
        } finally {
            $this->removerDiretorioRecursivamente(
                $diretorioTemporario
            );
        }
    }

    private function extrairTextoImagemPorOcr(
        string $caminhoCompleto
    ): string {
        return $this->executarTesseract($caminhoCompleto);
    }

    private function executarTesseract(
        string $caminhoImagem
    ): string {
        $processo = new Process([
            '/usr/bin/tesseract',
            $caminhoImagem,
            'stdout',
            '-l',
            'por+eng',
            '--psm',
            '6',
        ]);

        $processo->setTimeout(120);
        $processo->mustRun();

        return $processo->getOutput();
    }

    private function normalizarTextoExtraido(
        string $texto
    ): string {
        $texto = str_replace(
            ["\r\n", "\r"],
            "\n",
            $texto
        );

        $texto = preg_replace(
            "/[ \t]+/u",
            ' ',
            $texto
        ) ?? $texto;

        $texto = preg_replace(
            "/\n{3,}/u",
            "\n\n",
            $texto
        ) ?? $texto;

        return trim($texto);
    }

    private function contarCaracteresUteis(
        string $texto
    ): int {
        $textoSemEspacos = preg_replace(
            '/\s+/u',
            '',
            $texto
        ) ?? '';

        return mb_strlen($textoSemEspacos);
    }

    private function lerPlanilhaOuCsv(
        string $caminhoCompleto,
        string $extensao
    ): array {
        $linhas = [];

        if (in_array($extensao, ['xls', 'xlsx'], true)) {
            $spreadsheet = IOFactory::load($caminhoCompleto);
            $sheet = $spreadsheet->getActiveSheet();
            $dados = $sheet->toArray();

            array_shift($dados);

            foreach ($dados as $linha) {
                if (count(array_filter($linha)) === 0) {
                    continue;
                }

                $linhas[] = [
                    'categoria' => $this->limparTexto(
                        $linha[0] ?? 'Sem categoria'
                    ),
                    'nome' => $this->limparTexto(
                        $linha[1] ?? ''
                    ),
                    'descricao' => $this->limparTexto(
                        $linha[2] ?? ''
                    ),
                    'preco' => $this->formatarPreco(
                        $linha[3] ?? 0
                    ),
                ];
            }

            return $linhas;
        }

        $arquivo = fopen($caminhoCompleto, 'r');

        if ($arquivo === false) {
            return [];
        }

        $cabecalho = fgetcsv($arquivo, 0, ';');

        if (!$cabecalho || count($cabecalho) < 3) {
            fclose($arquivo);

            abort(
                422,
                'Arquivo inválido. Use: categoria;produto;descricao;preco'
            );
        }

        while (($linha = fgetcsv($arquivo, 0, ';')) !== false) {
            if (count(array_filter($linha)) === 0) {
                continue;
            }

            $linhas[] = [
                'categoria' => $this->limparTexto(
                    $linha[0] ?? 'Sem categoria'
                ),
                'nome' => $this->limparTexto(
                    $linha[1] ?? ''
                ),
                'descricao' => $this->limparTexto(
                    $linha[2] ?? ''
                ),
                'preco' => $this->formatarPreco(
                    $linha[3] ?? 0
                ),
            ];
        }

        fclose($arquivo);

        return $linhas;
    }

    private function calcularEstatisticas(
        array $linhas,
        int $restauranteId
    ): array {
        $categoriasArquivo = collect($linhas)
            ->pluck('categoria')
            ->filter()
            ->unique()
            ->values();

        $produtosArquivo = collect($linhas)
            ->pluck('nome')
            ->filter()
            ->unique()
            ->values();

        $categoriasExistentes = Categoria::where(
            'restaurante_id',
            $restauranteId
        )
            ->whereIn('nome', $categoriasArquivo)
            ->count();

        $produtosExistentes = Produto::whereIn(
            'nome',
            $produtosArquivo
        )
            ->whereHas('categoria', function ($query) use (
                $restauranteId
            ) {
                $query->where(
                    'restaurante_id',
                    $restauranteId
                );
            })
            ->count();

        return [
            'categorias_total' => $categoriasArquivo->count(),
            'categorias_novas' => max(
                0,
                $categoriasArquivo->count()
                - $categoriasExistentes
            ),
            'produtos_total' => $produtosArquivo->count(),
            'produtos_novos' => max(
                0,
                $produtosArquivo->count()
                - $produtosExistentes
            ),
            'produtos_atualizados' => $produtosExistentes,
        ];
    }


    private function listaParaTexto(mixed $valor): ?string
    {
        if (is_array($valor)) {
            $valor = collect($valor)
                ->map(
                    fn($item) => trim((string) $item)
                )
                ->filter()
                ->unique(
                    fn($item) => mb_strtolower($item)
                )
                ->implode(', ');
        }

        $texto = trim((string) $valor);

        return $texto !== '' ? $texto : null;
    }

    private function removerArquivoTemporarioAnterior(): void
    {
        $arquivoAnterior = session('importacao_arquivo');

        if (!is_array($arquivoAnterior)) {
            return;
        }

        foreach (['caminho', 'texto_caminho'] as $chave) {
            if (
                !empty($arquivoAnterior[$chave])
                && Storage::disk('local')->exists(
                    $arquivoAnterior[$chave]
                )
            ) {
                Storage::disk('local')->delete(
                    $arquivoAnterior[$chave]
                );
            }
        }
    }

    private function removerDiretorioRecursivamente(
        string $diretorio
    ): void {
        if (!is_dir($diretorio)) {
            return;
        }

        $arquivos = array_diff(
            scandir($diretorio) ?: [],
            ['.', '..']
        );

        foreach ($arquivos as $arquivo) {
            $caminho = $diretorio . DIRECTORY_SEPARATOR . $arquivo;

            if (is_dir($caminho)) {
                $this->removerDiretorioRecursivamente(
                    $caminho
                );
            } else {
                @unlink($caminho);
            }
        }

        @rmdir($diretorio);
    }

    private function formatarPreco($valor): float
    {
        $valor = (string) $valor;
        $valor = str_replace(['R$', ' '], '', $valor);

        if (
            str_contains($valor, ',')
            && str_contains($valor, '.')
        ) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        } elseif (str_contains($valor, ',')) {
            $valor = str_replace(',', '.', $valor);
        }

        return (float) $valor;
    }

    private function limparTexto($texto): string
    {
        return mb_convert_encoding(
            trim((string) $texto),
            'UTF-8',
            'Windows-1252,ISO-8859-1,UTF-8'
        );
    }
}