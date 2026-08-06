<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Restaurante extends Model
{
    protected $fillable = [
        'user_id',
        'account_id',
        'nome',
        'telefone',
        'documento',
        'email',
        'cep',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'instagram',
        'site',
        'taxa_entrega',
        'tempo_medio',
        'logo',
        'ativo',
        'delivery',
        'retirada',
        'consumo_local',
        'quantidade_mesas',
        'slug',
        'banner',
        'segmento',
        'cor_primaria',
        'cor_secundaria',
        'onboarding_concluido',
        'abre_as',
        'fecha_as',
        'google_rating',
        'google_reviews_total',
        'google_maps_url',
        'plano',
        'evolution_instance',
        'evolution_status',
        'evolution_phone',
        'evolution_connected_at',
        'evolution_last_sync_at',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'delivery' => 'boolean',
        'retirada' => 'boolean',
        'consumo_local' => 'boolean',
        'onboarding_concluido' => 'boolean',
        'evolution_connected_at' => 'datetime',
        'evolution_last_sync_at' => 'datetime',
    ];

    public function possuiWhatsappConectado(): bool
    {
        return $this->evolution_status === 'open';
    }
    public function usaWhatsappComIA(): bool
    {
        return in_array($this->plano, ['MENU_IA', 'FOOD'], true);
    }
    public function nomeInstanciaEvolution(): string
    {
        return $this->evolution_instance ?? 'rima_rest_' . $this->id;
    }
    public function categorias()
    {
        return $this->hasMany(Categoria::class);
    }
    public function produtos()
    {
        return $this->hasManyThrough(Produto::class, Categoria::class);
    }
    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }
    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }
    public function campanhas()
    {
        return $this->hasMany(Campanha::class);
    }
    public function configuracaoEntrega()
    {
        return $this->hasOne(ConfiguracaoEntrega::class);
    }
    public function temIA(): bool
    {
        return in_array($this->plano, ['MENU_IA', 'FOOD'], true);
    }
    public function ehFood(): bool
    {
        return $this->plano === 'FOOD';
    }
    public function ehMenu(): bool
    {
        return $this->plano === 'MENU';
    }

    public function getBannerUrlAttribute(): ?string
    {
        if (!$this->banner) {
            return null;
        }

        if (Str::startsWith($this->banner, ['/images/', 'images/'])) {
            return asset(ltrim($this->banner, '/'));
        }

        return Storage::url($this->banner);
    }
}
