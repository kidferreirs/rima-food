<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            if (! Schema::hasColumn('pedidos', 'forma_pagamento')) {
                $table->string('forma_pagamento')
                    ->default('dinheiro')
                    ->after('origem');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            if (Schema::hasColumn('pedidos', 'forma_pagamento')) {
                $table->dropColumn('forma_pagamento');
            }
        });
    }
};