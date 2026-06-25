<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurantes', function (Blueprint $table) {
            if (!Schema::hasColumn('restaurantes', 'documento')) {
                $table->string('documento', 18)->nullable()->after('telefone');
            }

            if (!Schema::hasColumn('restaurantes', 'cidade')) {
                $table->string('cidade')->nullable()->after('email');
            }

            if (!Schema::hasColumn('restaurantes', 'estado')) {
                $table->string('estado', 2)->nullable()->after('cidade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurantes', function (Blueprint $table) {
            if (Schema::hasColumn('restaurantes', 'documento')) {
                $table->dropColumn('documento');
            }

            if (Schema::hasColumn('restaurantes', 'cidade')) {
                $table->dropColumn('cidade');
            }

            if (Schema::hasColumn('restaurantes', 'estado')) {
                $table->dropColumn('estado');
            }
        });
    }
};