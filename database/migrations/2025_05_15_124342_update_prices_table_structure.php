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
            $table->dropColumn('wholesale');
            $table->dropColumn('condition');
            /* $table->dropColumn('type'); */
            $table->enum('type', ['Aggregated', 'Scraped'])->default('Scraped')->change();
            $table->dropColumn('metadata');
            $table->date('date')->after('value');
            $table->decimal('value', 8, 2)->nullable(false)->change();
            $table->string('currency')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prices', function (Blueprint $table) {
            $table->decimal('wholesale', 8, 2)->nullable();
            $table->enum('condition', ['New', 'Used', 'Sealed', 'Mint', 'Good', 'Played', 'Unknown'])->nullable();
            /*   $table->string('type')->nullable(); */
            $table->json('metadata')->nullable();
            $table->dropColumn('date');
            $table->decimal('value', 8, 2)->nullable()->change();
            $table->string('currency')->nullable()->change();
        });
    }
};
