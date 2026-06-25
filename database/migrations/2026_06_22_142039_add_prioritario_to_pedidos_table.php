<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {

            if (! Schema::hasColumn('pedidos', 'prioritario')) {

                $table->boolean('prioritario')
                    ->default(false)
                    ->after('forma_pagamento');

            }

        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {

            if (Schema::hasColumn('pedidos', 'prioritario')) {

                $table->dropColumn('prioritario');

            }

        });
    }
};