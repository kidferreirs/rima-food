<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function itensPedido()
    {
        return $this->hasMany(ItemPedido::class);
    }
}