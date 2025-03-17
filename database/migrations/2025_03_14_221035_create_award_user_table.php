<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::create('award_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('award_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unique(['award_id', 'user_id']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('award_user');
    }
};
