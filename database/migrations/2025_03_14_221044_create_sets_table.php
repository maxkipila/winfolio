<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('set_num', 50)->unique();
            $table->string('name', 255)->notNull();
            $table->string('img_url', 500)->nullable();
            $table->integer('year')->nullable();
            $table->integer('num_parts')->nullable();
            $table->foreignId('review_id')->nullable()->constrained('reviews')->nullOnDelete();
            $table->foreignId('theme_id')->nullable()->constrained('themes')->nullOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sets');
    }
};
