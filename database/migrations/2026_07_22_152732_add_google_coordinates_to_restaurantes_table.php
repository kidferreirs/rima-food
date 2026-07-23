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
            if (!Schema::hasColumn('restaurantes', 'latitude')) {
                $table->decimal('latitude', 10, 7)
                    ->nullable()
                    ->after('estado');
            }

            if (!Schema::hasColumn('restaurantes', 'longitude')) {
                $table->decimal('longitude', 10, 7)
                    ->nullable()
                    ->after('latitude');
            }

            if (!Schema::hasColumn('restaurantes', 'google_place_id')) {
                $table->string('google_place_id')
                    ->nullable()
                    ->after('longitude');
            }
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurantes', function (Blueprint $table) {
            $colunas = [];

            if (Schema::hasColumn('restaurantes', 'latitude')) {
                $colunas[] = 'latitude';
            }

            if (Schema::hasColumn('restaurantes', 'longitude')) {
                $colunas[] = 'longitude';
            }

            if (Schema::hasColumn('restaurantes', 'google_place_id')) {
                $colunas[] = 'google_place_id';
            }

            if (!empty($colunas)) {
                $table->dropColumn($colunas);
            }
        });
    }
};
