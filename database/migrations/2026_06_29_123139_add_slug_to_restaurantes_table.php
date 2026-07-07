<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Restaurante;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurantes', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('nome');
        });

        Restaurante::all()->each(function ($restaurante) {
            $baseSlug = Str::slug($restaurante->nome);
            $slug = $baseSlug;
            $contador = 1;

            while (Restaurante::where('slug', $slug)->where('id', '!=', $restaurante->id)->exists()) {
                $slug = $baseSlug . '-' . $contador;
                $contador++;
            }

            $restaurante->update(['slug' => $slug]);
        });

        Schema::table('restaurantes', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('restaurantes', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};