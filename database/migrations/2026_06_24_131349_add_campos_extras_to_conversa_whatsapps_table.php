<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversa_whatsapps', function (Blueprint $table) {

            $table->text('ultima_mensagem')
                ->nullable()
                ->after('telefone');

            $table->timestamp('ultima_interacao')
                ->nullable()
                ->after('ultima_mensagem');

            $table->boolean('atendimento_humano')
                ->default(false)
                ->after('pedido_confirmado');

        });
    }

    public function down(): void
    {
        Schema::table('conversa_whatsapps', function (Blueprint $table) {

            $table->dropColumn([
                'ultima_mensagem',
                'ultima_interacao',
                'atendimento_humano',
            ]);

        });
    }
};