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
        Schema::create('set_minifigs', function (Blueprint $table) {
            $table->foreignId('parent_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity')->default(1)->nullable();
            $table->timestamps();

            $table->primary(['parent_id', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('set_minifigs');
    }
};
