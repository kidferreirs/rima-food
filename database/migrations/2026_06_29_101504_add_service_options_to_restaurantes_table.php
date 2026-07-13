<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('restaurantes', function (Blueprint $table) {

            $table->boolean('delivery')->default(true);

            $table->boolean('retirada')->default(true);

            $table->boolean('consumo_local')->default(false);

            $table->integer('quantidade_mesas')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
Schema::table('restaurantes', function (Blueprint $table) {
        $table->dropColumn([
            'delivery',
            'retirada',
            'consumo_local',
            'quantidade_mesas',
        ]);
    });
    }
};
