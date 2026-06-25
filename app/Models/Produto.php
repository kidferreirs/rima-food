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
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
}