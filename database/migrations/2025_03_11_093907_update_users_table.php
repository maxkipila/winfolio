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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->string('password')->nullable()->change();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('nickname')->nullable();
            $table->string('prefix')->nullable();
            $table->string('phone')->nullable();
            $table->string('day')->nullable();
            $table->string('month')->nullable();
            $table->string('year')->nullable();
            $table->string('street')->nullable();
            $table->string('street_2')->nullable();
            $table->string('psc')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name');
            $table->string('password')->nullable(false)->change();
            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
            $table->dropColumn('nickname');
            $table->dropColumn('prefix');
            $table->dropColumn('phone');
            $table->dropColumn('day');
            $table->dropColumn('month');
            $table->dropColumn('year');
            $table->dropColumn('street');
            $table->dropColumn('street_2');
            $table->dropColumn('psc');
            $table->dropColumn('city');
            $table->dropColumn('country');
        });
    }
};
