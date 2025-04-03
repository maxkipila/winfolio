<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('award_user', function (Blueprint $table) {
            $table->foreignId('award_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['award_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('award_user');
    }
};
