<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image')->nullable(); // imagens
            $table->foreignId('category_id')->constrained()->onDelete('cascade'); // categoria
            $table->foreignId('brand_id')->constrained()->onDelete('cascade'); // marca
            $table->integer('quantity')->default(0); // estoque
            $table->decimal('cost_value', 10, 2);    // valor de custo
            $table->decimal('sale_value', 10, 2);    // valor de venda
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
