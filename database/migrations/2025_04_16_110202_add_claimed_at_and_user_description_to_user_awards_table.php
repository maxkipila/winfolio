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
        Schema::table('user_awards', function (Blueprint $table) {
            $table->timestamp('claimed_at')->nullable()->after('earned_at');
            $table->text('user_description')->nullable()->after('notified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_awards', function (Blueprint $table) {
            $table->dropColumn('claimed_at');
            $table->dropColumn('user_description');
        });
    }
};
