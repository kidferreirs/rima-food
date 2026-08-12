<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversa_whatsapps', function (Blueprint $table) {
            $table->string('cep_entrega', 9)
                ->nullable()
                ->after('tipo_entrega');

            $table->decimal('taxa_entrega', 10, 2)
                ->default(0)
                ->after('endereco_entrega');
        });
    }

    public function down(): void
    {
        Schema::table('conversa_whatsapps', function (Blueprint $table) {
            $table->dropColumn([
                'cep_entrega',
                'taxa_entrega',
            ]);
        });
    }
};