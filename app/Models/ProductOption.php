<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOption extends Model
{
    protected $fillable = [
        'product_option_group_id',
        'nome',
        'valor',
        'ordem',
        'ativo',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'ordem' => 'integer',
        'ativo' => 'boolean',
    ];

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(
            ProductOptionGroup::class,
            'product_option_group_id'
        );
    }
}