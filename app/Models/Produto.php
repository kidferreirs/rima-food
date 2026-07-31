<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produto extends Model
{
    protected $fillable = [
        'categoria_id',
        'nome',
        'descricao',
        'preco',
        'imagem',
        'ativo',
        'palavras_chave',
        'sinonimos',
        'ingredientes',
        'restricoes',
        'tags',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'preco' => 'decimal:2',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function itensPedido()
    {
        return $this->hasMany(ItemPedido::class);
    }

    public function gruposOpcoes(): HasMany
    {
        return $this->hasMany(
            ProductOptionGroup::class,
            'produto_id'
        )->orderBy('ordem');
    }

    public function gruposOpcoesAtivos(): HasMany
    {
        return $this->gruposOpcoes()
            ->where('ativo', true);
    }
}