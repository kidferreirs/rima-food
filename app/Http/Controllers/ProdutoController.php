<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProdutoController extends BaseRestaurantController
{
    public function index()
    {
        $restaurante = $this->restaurante();

        $produtos = Produto::with('categoria')
            ->whereHas('categoria', function ($query) use ($restaurante) {
                $query->where('restaurante_id', $restaurante->id);
            })
            ->latest()
            ->get();

        return view('produtos.index', compact('produtos'));
    }

    public function create()
    {
        $restaurante = $this->restaurante();

        $categorias = Categoria::where('restaurante_id', $restaurante->id)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        return view('produtos.create', compact('categorias', 'restaurante'));
    }

    public function store(Request $request)
    {
        $restaurante = $this->restaurante();

        $dados = $request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'preco' => 'required|numeric|min:0',
            'imagem' => 'nullable|image|max:2048',
            'palavras_chave' => 'nullable|string',
            'sinonimos' => 'nullable|string',
            'ingredientes' => 'nullable|string',
            'restricoes' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        $this->autorizarCategoria($dados['categoria_id']);
        $dados['tags'] = implode(',', $dados['tags'] ?? []);

        if ($request->hasFile('imagem')) {
            $dados['imagem'] = $request
                ->file('imagem')
                ->store('produtos', 'public');
        }

        Produto::create($dados);

        return redirect()
            ->route('restaurante.produtos.index', $restaurante->slug)
            ->with('success', 'Produto cadastrado com sucesso!');
    }

    public function edit(string $slug, Produto $produto)
    {
        $restaurante = $this->restaurante();

        $this->autorizarProduto($produto);

        $categorias = Categoria::where('restaurante_id', $restaurante->id)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        return view('produtos.edit', compact('produto', 'categorias', 'restaurante'));
    }

    public function update(Request $request, string $slug, Produto $produto)
    {
        $restaurante = $this->restaurante();

        $this->autorizarProduto($produto);

        $dados = $request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'preco' => 'required|numeric|min:0',
            'imagem' => 'nullable|image|max:2048',

            'palavras_chave' => 'nullable|string',
            'sinonimos' => 'nullable|string',
            'ingredientes' => 'nullable|string',
            'restricoes' => 'nullable|string',

            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ]);

        $this->autorizarCategoria($dados['categoria_id']);

        $dados['tags'] = implode(',', $dados['tags'] ?? []);

        if ($request->hasFile('imagem')) {
            if ($produto->imagem) {
                Storage::disk('public')->delete($produto->imagem);
            }

            $dados['imagem'] = $request->file('imagem')->store('produtos', 'public');
        }

        $produto->update($dados);

        return redirect()
            ->route('restaurante.produtos.index', $restaurante->slug)
            ->with('success', 'Produto atualizado com sucesso!');
    }

    public function alterarStatus(string $slug, Produto $produto)
    {
        $restaurante = $this->restaurante();

        $this->autorizarProduto($produto);

        $produto->update([
            'ativo' => !$produto->ativo,
        ]);

        return redirect()
            ->route('restaurante.produtos.index', $restaurante->slug)
            ->with('success', 'Status do produto atualizado!');
    }

    private function autorizarCategoria($categoriaId): void
    {
        $categoriaExiste = Categoria::where('id', $categoriaId)
            ->where('restaurante_id', $this->restaurante()->id)
            ->exists();

        if (!$categoriaExiste) {
            abort(403);
        }
    }

    private function autorizarProduto(Produto $produto): void
    {
        $produtoValido = Produto::where('id', $produto->id)
            ->whereHas('categoria', function ($query) {
                $query->where('restaurante_id', $this->restaurante()->id);
            })
            ->exists();

        if (!$produtoValido) {
            abort(403);
        }
    }
}