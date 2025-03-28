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
            $table->string('product_num')->unique();
            $table->enum('product_type', ['minifig', 'set']);
            $table->string('name');
            $table->year('year')->nullable();
            $table->foreignId('theme_id')->nullable()->constrained();
            $table->integer('num_parts')->nullable();
            $table->string('img_url')->nullable();
            $table->enum('availability', ['Retail', 'Retired', 'Retiring soon', 'Unavailable', 'Coming soon'])->nullable();
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
