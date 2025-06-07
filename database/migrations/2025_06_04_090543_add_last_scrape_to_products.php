<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->timestamp('released_at')->nullable();
            $table->timestamp('scraped_at')->nullable();
            $table->string('availability')->nullable()->change();
            $table->float('used_price')->nullable();
            $table->string('used_range')->nullable();
            $table->json('scraped_imgs')->default(new Expression('(JSON_ARRAY())'));
            $table->json('facts')->default(new Expression('(JSON_ARRAY())'));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('released_at');
            $table->dropColumn('scraped_at');
            $table->dropColumn('used_price');
            $table->dropColumn('used_range');
            $table->dropColumn('scraped_imgs');
            $table->dropColumn('facts');
        });
    }
};
