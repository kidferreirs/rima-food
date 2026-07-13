<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('restaurantes', function (Blueprint $table) {
            $table->decimal('google_rating', 2, 1)->nullable()->after('estado');
            $table->integer('google_reviews_total')->nullable()->after('google_rating');
            $table->string('google_maps_url')->nullable()->after('google_reviews_total');
        });
    }

    public function down(): void
    {
        Schema::table('restaurantes', function (Blueprint $table) {
            $table->dropColumn([
                'google_rating',
                'google_reviews_total',
                'google_maps_url',
            ]);
        });
    }
};
