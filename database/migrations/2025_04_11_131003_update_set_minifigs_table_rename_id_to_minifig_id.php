<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Spustí migraci – aktualizuje tabulku set_minifigs.
     */
    public function up(): void
    {
        // 1) DROP FOREIGN KEYS a PRIMÁRNÍ KLÍČ
        Schema::table('set_minifigs', function (Blueprint $table) {
            // Nejprve odebereme cizí klíče
            $table->dropForeign(['parent_id']);   // Např. set_minifigs_parent_id_foreign
            $table->dropForeign(['id']);          // Např. set_minifigs_id_foreign

            // Poté odstraníme primární klíč složený z (parent_id, id)
            $table->dropPrimary(['parent_id', 'id']);
        });

        // 2) RENAME COLUMN z 'id' na 'minifig_id'
        Schema::table('set_minifigs', function (Blueprint $table) {
            $table->renameColumn('id', 'minifig_id');
        });

        // 3) ZNOVU PŘIDÁME FOREIGN KEYS a PRIMÁRNÍ KLÍČ
        Schema::table('set_minifigs', function (Blueprint $table) {
            // Cizí klíč pro 'parent_id' (odkazuje na tabulku 'products')
            $table->foreign('parent_id')
                ->references('id')->on('products')
                ->cascadeOnDelete();

            // Cizí klíč pro 'minifig_id' (bývalé 'id')
            $table->foreign('minifig_id')
                ->references('id')->on('products')
                ->cascadeOnDelete();

            // Primární klíč (parent_id, minifig_id)
            $table->primary(['parent_id', 'minifig_id']);
        });
    }

    /**
     * Vrátí migraci zpět – obnoví původní strukturu.
     */
    public function down(): void
    {

        Schema::table('set_minifigs', function (Blueprint $table) {

            $table->dropForeign(['parent_id']);
            $table->dropForeign(['minifig_id']);
            $table->dropPrimary(['parent_id', 'minifig_id']);
        });


        Schema::table('set_minifigs', function (Blueprint $table) {
            $table->renameColumn('minifig_id', 'id');
        });

        Schema::table('set_minifigs', function (Blueprint $table) {
            $table->foreign('parent_id')
                ->references('id')->on('products')
                ->cascadeOnDelete();

            $table->foreign('id')
                ->references('id')->on('products')
                ->cascadeOnDelete();

            $table->primary(['parent_id', 'id']);
        });
    }
};
