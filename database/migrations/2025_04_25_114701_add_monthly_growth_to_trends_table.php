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
        Schema::table('trends', function (Blueprint $table) {

            $table->decimal('monthly_growth', 8, 2)->nullable()->after('weekly_growth');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trends', function (Blueprint $table) {
            $table->dropColumn('monthly_growth');
        });
    }
};
