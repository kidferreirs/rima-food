<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionLog extends Model
{
    protected $fillable = [
        'subscription_id',
        'evento',
        'descricao',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}