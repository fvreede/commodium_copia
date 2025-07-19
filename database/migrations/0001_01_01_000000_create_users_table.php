<?php

/**
 * Bestandsnaam: 0001_01_01_000000_create_users_table.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2024-12-25
 * Tijd: 16:57:50
 * Doel: Database migration voor het aanmaken van de kern authenticatie tabellen. Bevat users tabel voor gebruikersaccounts, password_reset_tokens voor veilige wachtwoord resets en sessions tabel voor sessie management.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voer de database migrations uit
     * Maakt de kern tabellen aan voor gebruikersauthenticatie en sessie beheer
     * 
     * @return void
     */
    public function up(): void
    {
        // Maak users tabel aan voor gebruikersaccounts
        Schema::create('users', function (Blueprint $table) {
            $table->id();                                    // Auto-increment primary key
            $table->string('name');                          // Volledige naam van de gebruiker
            $table->string('email')->unique();              // Email adres (uniek voor login)
            $table->timestamp('email_verified_at')->nullable(); // Tijdstip van email verificatie
            $table->string('password');                     // Gehashed wachtwoord
            $table->rememberToken();                        // Token voor "onthoud mij" functionaliteit
            $table->timestamps();                           // created_at en updated_at timestamps
        });

        // Maak password_reset_tokens tabel aan voor veilige wachtwoord resets
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();             // Email als primary key
            $table->string('token');                        // Unieke reset token voor beveiliging
            $table->timestamp('created_at')->nullable();    // Wanneer token aangemaakt werd
        });

        // Maak sessions tabel aan voor database-based sessie opslag
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();                // Unieke sessie ID
            $table->foreignId('user_id')->nullable()->index(); // Gekoppelde gebruiker (null voor gasten)
            $table->string('ip_address', 45)->nullable();   // IP adres van de client (IPv4/IPv6)
            $table->text('user_agent')->nullable();         // Browser user agent string
            $table->longText('payload');                    // Geserializeerde sessie data
            $table->integer('last_activity')->index();      // Timestamp van laatste activiteit
        });
    }

    /**
     * Draai de migrations terug
     * Verwijdert alle tabellen die in up() aangemaakt zijn
     * 
     * @return void
     */
    public function down(): void
    {
        // Verwijder tabellen in omgekeerde volgorde om foreign key problemen te voorkomen
        Schema::dropIfExists('sessions');           // Eerst sessions (heeft user_id foreign key)
        Schema::dropIfExists('password_reset_tokens'); // Dan password reset tokens
        Schema::dropIfExists('users');              // Als laatste users tabel
    }
};