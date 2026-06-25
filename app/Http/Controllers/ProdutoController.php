<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Produto;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function index()
    {
        $produtos = Produto::with('categoria.restaurante')
            ->whereHas('categoria.restaurante', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->latest()
            ->get();

        return view('produtos.index', compact('produtos'));
    }

    public function create()
    {
        $categorias = Categoria::with('restaurante')
            ->whereHas('restaurante', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->where('ativo', true)
            ->get();

        return view('produtos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'preco' => 'required|numeric|min:0',
        ]);

        $this->validarCategoria($dados['categoria_id']);

        Produto::create($dados);

        return redirect()
            ->route('produtos.index')
            ->with('success', 'Produto cadastrado com sucesso!');
    }

    public function edit(Produto $produto)
    {
        $this->validarProduto($produto);

        $categorias = Categoria::with('restaurante')
            ->whereHas('restaurante', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->where('ativo', true)
            ->get();

        return view(
            'produtos.edit',
            compact(
                'produto',
                'categorias'
            )
        );
    }

    public function update(Request $request, Produto $produto)
    {
        $this->validarProduto($produto);

        $dados = $request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'preco' => 'required|numeric|min:0',
        ]);

        $this->validarCategoria($dados['categoria_id']);

        $produto->update($dados);

        return redirect()
            ->route('produtos.index')
            ->with('success', 'Produto atualizado com sucesso!');
    }

    public function alterarStatus(Produto $produto)
    {
        $this->validarProduto($produto);

        $produto->update([
            'ativo' => ! $produto->ativo,
        ]);

        return redirect()
            ->route('produtos.index')
            ->with('success', 'Status do produto atualizado!');
    }

    private function validarCategoria($categoriaId)
    {
        $existe = Categoria::where('id', $categoriaId)
            ->whereHas('restaurante', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->exists();

        if (! $existe) {
            abort(403);
        }
    }

    private function validarProduto(Produto $produto)
    {
        $ok = Produto::where('id', $produto->id)
            ->whereHas('categoria.restaurante', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->exists();

        if (! $ok) {
            abort(403);
        }
    }
}