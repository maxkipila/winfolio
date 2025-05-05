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
        Schema::create('lego_id_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('rebrickable_id')->index();
            $table->string('brickeconomy_id')->nullable()->index();
            $table->string('bricklink_id')->nullable()->index();
            $table->string('name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('rebrickable_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lego_id_mappings');
    }
};
