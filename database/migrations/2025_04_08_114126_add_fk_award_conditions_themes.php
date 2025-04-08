<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('award_conditions', function (Blueprint $table) {
            $table->foreign('category_id')
                ->references('id')->on('themes')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('award_conditions', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->foreign('category_id')
                ->references('id')->on('categories')
                ->onDelete('set null');
        });
    }
};
