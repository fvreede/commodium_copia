<?php

/**
 * Bestandsnaam: 2025_01_08_163328_add_status_to_users.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-01-13
 * Tijd: 19:19:05
 * Doel: Database migration om status kolom toe te voegen aan users tabel. Enables account status beheer voor activering/suspensie van gebruikersaccounts voor beveiliging en moderatie doeleinden.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voer de database migrations uit
     * Voegt status kolom toe aan users tabel voor account beheer
     * 
     * @return void
     */
    public function up(): void
    {
        // Wijzig users tabel om status kolom toe te voegen
        Schema::table('users', function (Blueprint $table) {
            // Enum kolom voor account status met beperkte geldige waarden
            $table->enum('status', ['active', 'suspended'])
                ->default('active');     // Nieuwe accounts zijn standaard actief
            
            // Mogelijke status waarden:
            // - 'active': Account is actief en gebruiker kan inloggen
            // - 'suspended': Account is geschorst, login wordt geblokkeerd
        });
    }

    /**
     * Draai de migrations terug
     * Verwijdert de status kolom uit de users tabel
     * 
     * @return void
     */
    public function down(): void
    {
        // Verwijder status kolom uit users tabel
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};