<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversa_whatsapps', function (Blueprint $table) {

            $table->json('historico')
                  ->nullable()
                  ->after('ultima_mensagem');

        });
    }

    public function down(): void
    {
        Schema::table('conversa_whatsapps', function (Blueprint $table) {

            $table->dropColumn('historico');

        });
    }
};