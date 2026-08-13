<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cliente_servicos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('servico_id')
                ->constrained('servicos')
                ->cascadeOnDelete();

            $table->string('status')
                ->default('ativo');

            $table->decimal('valor', 10, 2)
                ->nullable();

            $table->string('tipo_cobranca')
                ->default('unico');

            $table->date('data_inicio')
                ->nullable();

            $table->date('data_fim')
                ->nullable();

            $table->text('observacoes')
                ->nullable();

            $table->timestamps();

            $table->unique([
                'account_id',
                'servico_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_servicos');
    }
};