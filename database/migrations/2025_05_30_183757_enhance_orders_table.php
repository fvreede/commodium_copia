<?php

/**
 * Bestandsnaam: 2025_05_30_183757_enhance_orders_table.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-05-30
 * Tijd: 20:42:25
 * Doel: Database migration voor het uitbreiden van orders tabel met aanvullende velden. Voegt delivery_address, notes en order_number kolommen toe voor complete bestelling informatie, klant communicatie en gebruiksvriendelijke bestelnummers.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voer de database migrations uit
     * Voegt delivery_address, notes en order_number kolommen toe aan orders tabel
     * 
     * @return void
     */
    public function up(): void
    {
        // Wijzig orders tabel om aanvullende velden toe te voegen met veiligheidscontroles
        Schema::table('orders', function (Blueprint $table) {
            
            // Voeg delivery_address kolom toe als deze nog niet bestaat
            if (!Schema::hasColumn('orders', 'delivery_address')) {
                // JSON kolom voor gestructureerde opslag van bezorgadres informatie
                $table->json('delivery_address')->nullable();
                // Nullable omdat bestaande bestellingen geen adres hebben
                // JSON formaat allows flexibele adres structuur:
                // {"street": "Hoofdstraat", "number": "123", "city": "Amsterdam", "postal_code": "1000AB"}
            }
            
            // Voeg notes kolom toe als deze nog niet bestaat
            if (!Schema::hasColumn('orders', 'notes')) {
                // Text kolom voor klant opmerkingen en speciale instructies
                $table->text('notes')->nullable();
                // Nullable omdat niet elke bestelling opmerkingen heeft
                // Voorbeelden: "Bel aan bij de buren", "Voorzichtig met breekbare items"
            }
            
            // Voeg order_number kolom toe als deze nog niet bestaat
            if (!Schema::hasColumn('orders', 'order_number')) {
                // String kolom voor gebruiksvriendelijke bestelnummers
                $table->string('order_number')->unique()->nullable();
                // Unique constraint voorkomt duplicate bestelnummers
                // Nullable voor bestaande bestellingen (kunnen later gegenereerd worden)
                // Formaat bijvoorbeeld: "CC-2024-001234" voor Commodum Copia
            }
        });
    }

    /**
     * Draai de migrations terug
     * Verwijdert de delivery_address, notes en order_number kolommen uit orders tabel
     * 
     * @return void
     */
    public function down(): void
    {
        // Verwijder alle toegevoegde kolommen uit orders tabel
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_address', 'notes', 'order_number']);
        });
    }
};