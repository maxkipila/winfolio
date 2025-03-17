<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('minifigs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('fig_num')->unique();
            $table->string('name');
            $table->integer('num_parts')->nullable();
            $table->string('img_url')->nullable();
            $table->foreignId('review_id')->nullable()->constrained('reviews')->nullOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('minifigs');
    }
};
