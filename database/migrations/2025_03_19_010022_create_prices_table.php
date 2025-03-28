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
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->decimal('retail', 8, 2);
            $table->decimal('wholesale', 8, 2)->nullable();
            $table->decimal('value', 8, 2)->nullable();
            /* $table->string('condition')->nullable(); */
            $table->enum('condition', ['New', 'Used', 'Sealed', 'Mint', 'Good', 'Played', 'Unknown'])->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};
