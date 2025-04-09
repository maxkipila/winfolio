<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['trending', 'top_mover'])->index();
            $table->decimal('weekly_growth', 8, 2)->nullable();
            $table->decimal('annual_growth', 8, 2)->nullable();
            $table->integer('favorites_count')->default(0);
            $table->date('calculated_at');
            $table->timestamps();

            $table->unique(['product_id', 'type', 'calculated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trends');
    }
};
