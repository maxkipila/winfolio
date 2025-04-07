<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_user', function (Blueprint $table) {
            $table->string('purchase_day')->nullable();
            $table->string('purchase_month')->nullable();
            $table->string('purchase_year')->nullable();
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->string('condition')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('product_user', function (Blueprint $table) {
            $table->dropColumn([
                'purchase_day',
                'purchase_month',
                'purchase_year',
                'purchase_price',
                'currency',
                'condition',
            ]);
        });
    }
};
