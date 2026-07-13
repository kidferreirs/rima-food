<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends BaseRestaurantController
{
    public function index()
    {
        $restaurante = $this->restaurante();

        $categorias = Categoria::where('restaurante_id', $restaurante->id)
            ->latest()
            ->get();

        return view('categorias.index', compact('categorias'));
    }

    public function create()
    {
        $restaurante = $this->restaurante();

        return view('categorias.create', compact('restaurante'));
    }

    public function store(Request $request)
    {
        $restaurante = $this->restaurante();

        $dados = $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        $dados['restaurante_id'] = $restaurante->id;

        Categoria::create($dados);

        return redirect()
            ->route('restaurante.categorias.index', $restaurante->slug)
            ->with('success', 'Categoria cadastrada com sucesso!');
    }

    public function edit(Categoria $categoria)
    {
        $restaurante = $this->restaurante();

        $this->autorizarCategoria($categoria);

        return view('categorias.edit', compact('categoria', 'restaurante'));
    }

    public function update(Request $request, Categoria $categoria)
    {
        $restaurante = $this->restaurante();

        $this->autorizarCategoria($categoria);

        $dados = $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        $categoria->update($dados);

        return redirect()
            ->route('restaurante.categorias.index', $restaurante->slug)
            ->with('success', 'Categoria atualizada com sucesso!');
    }

    public function alterarStatus(Categoria $categoria)
    {
        $restaurante = $this->restaurante();

        $this->autorizarCategoria($categoria);

        $categoria->update([
            'ativo' => ! $categoria->ativo,
        ]);

        return redirect()
            ->route('restaurante.categorias.index', $restaurante->slug)
            ->with('success', 'Status da categoria atualizado!');
    }

    private function autorizarCategoria(Categoria $categoria): void
    {
        if ($categoria->restaurante_id !== $this->restaurante()->id) {
            abort(403);
        }
    }
}