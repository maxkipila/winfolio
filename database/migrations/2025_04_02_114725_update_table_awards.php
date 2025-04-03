<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('awards', function (Blueprint $table) {
            $table->string('category')->nullable()->after('type');
            $table->string('icon')->nullable()->after('description');
        });
    }

    public function down()
    {
        Schema::table('awards', function (Blueprint $table) {
            $table->dropColumn(['category', 'icon']);
        });
    }
};
