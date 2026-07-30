<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductOptionGroup extends Model
{
    protected $fillable = [
        'produto_id',
        'nome',
        'tipo',
        'minimo',
        'maximo',
        'ordem',
        'ativo',
    ];

    protected $casts = [
        'minimo' => 'integer',
        'maximo' => 'integer',
        'ordem' => 'integer',
        'ativo' => 'boolean',
    ];

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }

    public function opcoes(): HasMany
    {
        return $this->hasMany(
            ProductOption::class,
            'product_option_group_id'
        )->orderBy('ordem');
    }

    public function opcoesAtivas(): HasMany
    {
        return $this->opcoes()
            ->where('ativo', true);
    }
}