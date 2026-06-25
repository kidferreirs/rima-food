<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('email')->nullable()->after('telefone');
            $table->text('observacao')->nullable()->after('cidade');
            $table->decimal('total_gasto', 10, 2)->default(0)->after('observacao');
            $table->boolean('ativo')->default(true)->after('total_gasto');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn([
                'email',
                'observacao',
                'total_gasto',
                'ativo',
            ]);
        });
    }
};