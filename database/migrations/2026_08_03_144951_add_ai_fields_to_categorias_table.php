<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->text('sinonimos')
                ->nullable()
                ->after('nome');

            $table->text('palavras_chave')
                ->nullable()
                ->after('sinonimos');
        });
    }

    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropColumn([
                'sinonimos',
                'palavras_chave',
            ]);
        });
    }
};