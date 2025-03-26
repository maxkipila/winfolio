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
        Schema::table('prices', function (Blueprint $table) {
            if (Schema::hasColumn('prices', 'set_id')) {
                $table->dropForeign(['set_id']);
                $table->dropColumn('set_id');
            }

            if (Schema::hasColumn('prices', 'minifig_id')) {
                $table->dropForeign(['minifig_id']);
                $table->dropColumn('minifig_id');
            }

            $table->foreignId('product_id')
                ->constrained('products')
                ->after('stav');

            $table->string('condition')->nullable()->after('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prices', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
            $table->dropColumn('condition');

            $table->unsignedBigInteger('set_id')->nullable()->after('stav');
            $table->unsignedBigInteger('minifig_id')->nullable()->after('set_id');
        });
    }
};
