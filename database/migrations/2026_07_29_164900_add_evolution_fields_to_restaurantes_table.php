<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurantes', function (Blueprint $table) {
            $table->string('evolution_instance')
                ->nullable()
                ->unique()
                ->after('plano');

            $table->string('evolution_status')
                ->nullable()
                ->after('evolution_instance');

            $table->string('evolution_phone')
                ->nullable()
                ->after('evolution_status');

            $table->timestamp('evolution_connected_at')
                ->nullable()
                ->after('evolution_phone');

            $table->timestamp('evolution_last_sync_at')
                ->nullable()
                ->after('evolution_connected_at');
        });
    }

    public function down(): void
    {
        Schema::table('restaurantes', function (Blueprint $table) {
            $table->dropUnique([
                'evolution_instance',
            ]);

            $table->dropColumn([
                'evolution_instance',
                'evolution_status',
                'evolution_phone',
                'evolution_connected_at',
                'evolution_last_sync_at',
            ]);
        });
    }
};