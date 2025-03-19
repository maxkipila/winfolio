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
        Schema::create('prices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('set_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('minifig_id')->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('retail', 8, 2)->nullable();
            $table->decimal('value', 8, 2)->nullable();
            $table->string('stav')->nullable();
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
