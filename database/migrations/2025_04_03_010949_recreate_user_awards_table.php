<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_awards', function (Blueprint $table) {
            $table->foreignId('award_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('earned_at')->nullable();
            $table->boolean('notified')->default(false);
            $table->integer('count')->nullable();
            $table->decimal('value', 15, 2)->nullable();
            $table->decimal('percentage', 8, 2)->nullable();
            $table->timestamps();
            $table->primary(['award_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_awards');
    }
};
