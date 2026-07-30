<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_option_groups', function (Blueprint $table) {

            $table->id();

            $table->foreignId('produto_id')
                ->constrained('produtos')
                ->cascadeOnDelete();

            $table->string('nome');

            $table->enum('tipo', [
                'CHECKBOX',
                'RADIO',
                'TEXT',
            ])->default('CHECKBOX');

            $table->unsignedTinyInteger('minimo')->default(0);

            $table->unsignedTinyInteger('maximo')->default(1);

            $table->unsignedInteger('ordem')->default(0);

            $table->boolean('ativo')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_option_groups');
    }
};
