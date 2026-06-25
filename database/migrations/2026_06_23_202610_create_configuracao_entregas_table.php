<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracao_entregas', function (Blueprint $table) {

            $table->id();

            $table->foreignId('restaurante_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('ate_5km', 10, 2)
                ->default(5);

            $table->decimal('ate_10km', 10, 2)
                ->default(8);

            $table->decimal('acima_10km', 10, 2)
                ->default(12);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracao_entregas');
    }
};