<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurantes', function (Blueprint $table) {

            $table->string('instagram')->nullable();

            $table->string('site')->nullable();

            $table->decimal('taxa_entrega', 8, 2)
                ->default(0);

            $table->integer('tempo_medio')
                ->default(30);

            $table->string('logo')->nullable();

        });
    }

    public function down(): void
    {
        Schema::table('restaurantes', function (Blueprint $table) {

            $table->dropColumn([

                'instagram',

                'site',

                'taxa_entrega',

                'tempo_medio',

                'logo',

            ]);

        });
    }
};