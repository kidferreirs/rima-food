<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_options', function (Blueprint $table) {

            $table->id();

            $table->foreignId('product_option_group_id')
                ->constrained('product_option_groups')
                ->cascadeOnDelete();

            $table->string('nome');

            $table->decimal('valor', 10, 2)
                ->default(0);

            $table->unsignedInteger('ordem')
                ->default(0);

            $table->boolean('ativo')
                ->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_options');
    }
};