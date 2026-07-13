<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('restaurantes', function (Blueprint $table) {
            $table->string('banner')->nullable()->after('logo');
            $table->time('abre_as')->nullable()->after('tempo_medio');
            $table->time('fecha_as')->nullable()->after('abre_as');
        });
    }

    public function down(): void
    {
        Schema::table('restaurantes', function (Blueprint $table) {
            $table->dropColumn([
                'banner',
                'abre_as',
                'fecha_as',
            ]);
        });
    }
};
