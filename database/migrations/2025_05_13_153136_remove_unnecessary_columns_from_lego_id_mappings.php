<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        DB::statement("
            UPDATE lego_id_mappings lm 
            JOIN products p ON lm.rebrickable_id = p.product_num 
            SET lm.product_id = p.id 
            WHERE lm.product_id IS NULL
        ");


        Schema::table('lego_id_mappings', function (Blueprint $table) {

            if (Schema::hasIndex('lego_id_mappings', 'lego_id_mappings_rebrickable_id_index')) {
                $table->dropIndex('lego_id_mappings_rebrickable_id_index');
            }
            if (Schema::hasIndex('lego_id_mappings', 'lego_id_mappings_bricklink_id_index')) {
                $table->dropIndex('lego_id_mappings_bricklink_id_index');
            }
            if (Schema::hasIndex('lego_id_mappings', 'lego_id_mappings_rebrickable_id_unique')) {
                $table->dropUnique('lego_id_mappings_rebrickable_id_unique');
            }


            $table->dropColumn(['rebrickable_id', 'bricklink_id', 'name', 'notes']);


            $table->unsignedBigInteger('product_id')->nullable(false)->change();
            $table->unique('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lego_id_mappings', function (Blueprint $table) {

            $table->dropUnique(['product_id']);


            $table->string('rebrickable_id')->nullable()->after('product_id');
            $table->string('bricklink_id')->nullable()->after('brickeconomy_id');
            $table->string('name')->nullable()->after('bricklink_id');
            $table->text('notes')->nullable()->after('name');


            DB::statement("
                UPDATE lego_id_mappings lm 
                JOIN products p ON lm.product_id = p.id 
                SET lm.rebrickable_id = p.product_num
            ");

            $table->index('rebrickable_id');
            $table->index('bricklink_id');
        });
    }
};
