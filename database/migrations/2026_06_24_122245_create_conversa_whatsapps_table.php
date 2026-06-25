<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversa_whatsapps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('restaurante_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('telefone');

            $table->string('nome_cliente')->nullable();

            $table->string('estado')->default('inicio');

            $table->json('carrinho')->nullable();

            $table->string('tipo_entrega')->nullable();

            $table->text('endereco_entrega')->nullable();

            $table->string('forma_pagamento')->nullable();

            $table->boolean('pedido_confirmado')->default(false);

            $table->foreignId('pedido_id')
                ->nullable()
                ->constrained('pedidos')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['restaurante_id', 'telefone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversa_whatsapps');
    }
};