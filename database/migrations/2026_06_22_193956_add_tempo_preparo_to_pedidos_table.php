<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {

            if (! Schema::hasColumn('pedidos', 'novo_em')) {

                $table->timestamp('novo_em')->nullable();

            }

            if (! Schema::hasColumn('pedidos', 'preparando_em')) {

                $table->timestamp('preparando_em')->nullable();

            }

            if (! Schema::hasColumn('pedidos', 'pronto_em')) {

                $table->timestamp('pronto_em')->nullable();

            }

            if (! Schema::hasColumn('pedidos', 'finalizado_em')) {

                $table->timestamp('finalizado_em')->nullable();

            }

        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {

            $table->dropColumn([
                'novo_em',
                'preparando_em',
                'pronto_em',
                'finalizado_em'
            ]);

        });
    }
};