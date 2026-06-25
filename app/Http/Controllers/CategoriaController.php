<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Restaurante;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::with('restaurante')
            ->whereHas('restaurante', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->latest()
            ->get();

        return view('categorias.index', compact('categorias'));
    }

    public function create()
    {
        $restaurantes = Restaurante::where('user_id', auth()->id())
            ->where('ativo', true)
            ->get();

        return view('categorias.create', compact('restaurantes'));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'restaurante_id' => 'required|exists:restaurantes,id',
            'nome' => 'required|string|max:255',
        ]);

        $this->validarRestauranteDoUsuario($dados['restaurante_id']);

        Categoria::create($dados);

        return redirect()
            ->route('categorias.index')
            ->with('success', 'Categoria cadastrada com sucesso!');
    }

    public function edit(Categoria $categoria)
    {
        $this->validarRestauranteDoUsuario($categoria->restaurante_id);

        $restaurantes = Restaurante::where('user_id', auth()->id())
            ->where('ativo', true)
            ->get();

        return view('categorias.edit', compact('categoria', 'restaurantes'));
    }

    public function update(Request $request, Categoria $categoria)
    {
        $this->validarRestauranteDoUsuario($categoria->restaurante_id);

        $dados = $request->validate([
            'restaurante_id' => 'required|exists:restaurantes,id',
            'nome' => 'required|string|max:255',
        ]);

        $this->validarRestauranteDoUsuario($dados['restaurante_id']);

        $categoria->update($dados);

        return redirect()
            ->route('categorias.index')
            ->with('success', 'Categoria atualizada com sucesso!');
    }

    public function alterarStatus(Categoria $categoria)
    {
        $this->validarRestauranteDoUsuario($categoria->restaurante_id);

        $categoria->update([
            'ativo' => ! $categoria->ativo,
        ]);

        return redirect()
            ->route('categorias.index')
            ->with('success', 'Status da categoria atualizado!');
    }

    private function validarRestauranteDoUsuario($restauranteId)
    {
        $existe = Restaurante::where('id', $restauranteId)
            ->where('user_id', auth()->id())
            ->exists();

        if (! $existe) {
            abort(403);
        }
    }
}