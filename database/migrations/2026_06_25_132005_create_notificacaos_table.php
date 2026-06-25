<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificacaos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('restaurante_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('pedido_id')
                ->nullable()
                ->constrained('pedidos')
                ->nullOnDelete();

            $table->string('tipo')->default('novo_pedido');

            $table->string('titulo');

            $table->text('mensagem')->nullable();

            $table->boolean('lida')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacaos');
    }
};