<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            if (! Schema::hasColumn('pedidos', 'tipo_entrega')) {
                $table->string('tipo_entrega')->default('balcao')->after('origem');
            }

            if (! Schema::hasColumn('pedidos', 'taxa_entrega')) {
                $table->decimal('taxa_entrega', 10, 2)->default(0)->after('tipo_entrega');
            }

            if (! Schema::hasColumn('pedidos', 'endereco_entrega')) {
                $table->text('endereco_entrega')->nullable()->after('taxa_entrega');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            if (Schema::hasColumn('pedidos', 'tipo_entrega')) {
                $table->dropColumn('tipo_entrega');
            }

            if (Schema::hasColumn('pedidos', 'taxa_entrega')) {
                $table->dropColumn('taxa_entrega');
            }

            if (Schema::hasColumn('pedidos', 'endereco_entrega')) {
                $table->dropColumn('endereco_entrega');
            }
        });
    }
};