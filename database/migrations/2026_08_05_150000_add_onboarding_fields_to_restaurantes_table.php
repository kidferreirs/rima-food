<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurantes', function (Blueprint $table) {
            $table->string('segmento')->nullable()->after('banner');
            $table->string('cor_primaria', 20)->nullable()->after('segmento');
            $table->string('cor_secundaria', 20)->nullable()->after('cor_primaria');
            $table->boolean('onboarding_concluido')->default(false)->after('cor_secundaria');
        });
    }

    public function down(): void
    {
        Schema::table('restaurantes', function (Blueprint $table) {
            $table->dropColumn(['segmento', 'cor_primaria', 'cor_secundaria', 'onboarding_concluido']);
        });
    }
};
