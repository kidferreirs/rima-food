<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CardapioImportController extends BaseRestaurantController
{
    public function index()
    {
        $restaurante = $this->restaurante();

        return view('importacao.cardapio', [
            'restaurante' => $restaurante,
            'linhas' => session('importacao_cardapio', []),
            'estatisticas' => session('importacao_estatisticas', []),
        ]);
    }

    public function preview(Request $request)
    {
        $restaurante = $this->restaurante();

        $request->validate([
            'arquivo' => 'required|file|mimes:csv,txt,xlsx|max:4096',
        ]);

        $arquivoUpload = $request->file('arquivo');
        $extensao = strtolower($arquivoUpload->getClientOriginalExtension());

        $linhas = [];

        if ($extensao === 'xlsx') {
            $spreadsheet = IOFactory::load($arquivoUpload->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();

            $dados = $sheet->toArray();

            array_shift($dados); // remove cabeçalho

            foreach ($dados as $linha) {
                if (count(array_filter($linha)) === 0) {
                    continue;
                }

                $linhas[] = [
                    'categoria' => $this->limparTexto($linha[0] ?? 'Sem categoria'),
                    'nome' => $this->limparTexto($linha[1] ?? ''),
                    'descricao' => $this->limparTexto($linha[2] ?? ''),
                    'preco' => $this->formatarPreco($linha[3] ?? 0),
                ];
            }
        } else {
            $arquivo = fopen($arquivoUpload->getRealPath(), 'r');

            $cabecalho = fgetcsv($arquivo, 0, ';');

            if (!$cabecalho || count($cabecalho) < 3) {
                return back()->with('error', 'Arquivo inválido. Use: categoria;produto;descricao;preco');
            }

            while (($linha = fgetcsv($arquivo, 0, ';')) !== false) {
                if (count(array_filter($linha)) === 0) {
                    continue;
                }

                $linhas[] = [
                    'categoria' => $this->limparTexto($linha[0] ?? 'Sem categoria'),
                    'nome' => $this->limparTexto($linha[1] ?? ''),
                    'descricao' => $this->limparTexto($linha[2] ?? ''),
                    'preco' => $this->formatarPreco($linha[3] ?? 0),
                ];
            }

            fclose($arquivo);
        }

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

        $categoriasExistentes = Categoria::where('restaurante_id', $restaurante->id)
            ->whereIn('nome', $categoriasArquivo)
            ->count();

        $produtosExistentes = Produto::whereIn('nome', $produtosArquivo)
            ->whereHas('categoria', function ($query) use ($restaurante) {
                $query->where('restaurante_id', $restaurante->id);
            })
            ->count();

        $estatisticas = [
            'categorias_total' => $categoriasArquivo->count(),
            'categorias_novas' => max(0, $categoriasArquivo->count() - $categoriasExistentes),
            'produtos_total' => $produtosArquivo->count(),
            'produtos_novos' => max(0, $produtosArquivo->count() - $produtosExistentes),
            'produtos_atualizados' => $produtosExistentes,
        ];

        session([
            'importacao_cardapio' => $linhas,
            'importacao_estatisticas' => $estatisticas,
        ]);

        return view('importacao.cardapio', compact('restaurante', 'linhas', 'estatisticas'));
    }

    public function importar()
    {
        $restaurante = $this->restaurante();

        $linhas = session('importacao_cardapio', []);

        if (empty($linhas)) {
            return back()->with('error', 'Nenhum item para importar.');
        }

        $categoriasCriadas = 0;
        $produtosCriados = 0;
        $produtosAtualizados = 0;

        DB::transaction(function () use ($linhas, $restaurante, &$categoriasCriadas, &$produtosCriados, &$produtosAtualizados) {
            foreach ($linhas as $linha) {
                if (empty($linha['nome'])) {
                    continue;
                }

                $categoria = Categoria::where('restaurante_id', $restaurante->id)
                    ->where('nome', $linha['categoria'] ?: 'Sem categoria')
                    ->first();

                if (!$categoria) {
                    $categoria = Categoria::create([
                        'restaurante_id' => $restaurante->id,
                        'nome' => $linha['categoria'] ?: 'Sem categoria',
                        'ativo' => true,
                    ]);

                    $categoriasCriadas++;
                }

                $produto = Produto::where('categoria_id', $categoria->id)
                    ->where('nome', $linha['nome'])
                    ->first();

                if ($produto) {
                    $produto->update([
                        'descricao' => $linha['descricao'],
                        'preco' => $linha['preco'],
                        'ativo' => true,
                    ]);

                    $produtosAtualizados++;
                } else {
                    Produto::create([
                        'categoria_id' => $categoria->id,
                        'nome' => $linha['nome'],
                        'descricao' => $linha['descricao'],
                        'preco' => $linha['preco'],
                        'ativo' => true,
                    ]);

                    $produtosCriados++;
                }
            }
        });

        session()->forget([
            'importacao_cardapio',
            'importacao_estatisticas',
        ]);

        return redirect()
            ->route('restaurante.importacao.cardapio', $restaurante->slug)
            ->with('success', "Importação concluída! Categorias criadas: {$categoriasCriadas}. Produtos criados: {$produtosCriados}. Produtos atualizados: {$produtosAtualizados}.");
    }

    private function formatarPreco($valor): float
    {
        $valor = str_replace(['R$', ' '], '', $valor);
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);

        return (float) $valor;
    }

    private function limparTexto($texto): string
    {
        return mb_convert_encoding(
            trim($texto),
            'UTF-8',
            'Windows-1252,ISO-8859-1,UTF-8'
        );
    }
    public function modelo()
    {
        $conteudo = "categoria;produto;descricao;preco\n";
        $conteudo .= "Bebidas;Coca Cola 350ml;Lata bem gelada;5,00\n";
        $conteudo .= "Hambúrgueres;X Burguer;Pão, carne e queijo;24,90\n";

        return response($conteudo)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename=\"modelo-cardapio.csv\"');
    }
}