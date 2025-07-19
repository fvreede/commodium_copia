<?php

/**
 * Bestandsnaam: 2025_05_30_183959_enhance_order_items_table.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-05-30
 * Tijd: 20:42:25
 * Doel: Database migration voor het toevoegen van product_name kolom aan order_items tabel. Verbetert historische data integriteit door productnamen op te slaan op het moment van bestelling, zodat order details accuraat blijven ook als producten later worden gewijzigd of verwijderd.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voer de database migrations uit
     * Voegt product_name kolom toe aan order_items tabel voor historische data behoud
     * 
     * @return void
     */
    public function up(): void
    {
        // Wijzig order_items tabel om product_name kolom toe te voegen met veiligheidscontrole
        Schema::table('order_items', function (Blueprint $table) {
            
            // Voeg product_name kolom toe als deze nog niet bestaat
            if (!Schema::hasColumn('order_items', 'product_name')) {
                // String kolom voor productnaam op het moment van bestelling
                $table->string('product_name')->nullable();
                
                // Waarom deze kolom cruciaal is voor data integriteit:
                // 1. Producten kunnen worden hernoemd na bestelling
                // 2. Producten kunnen soft deleted worden 
                // 3. Product catalog kan worden geherstructureerd
                // 4. Order historiek moet altijd accuraat blijven voor:
                //    - Klant order overzichten
                //    - Facturen en rapporten
                //    - Customer service referenties
                //    - Juridische/accounting doeleinden
                
                // Nullable omdat bestaande order_items geen opgeslagen productnaam hebben
                // Deze kunnen later gevuld worden via data migration script indien gewenst
            }
        });
    }

    /**
     * Draai de migrations terug
     * Verwijdert de product_name kolom uit order_items tabel
     * 
     * @return void
     */
    public function down(): void
    {
        // Verwijder product_name kolom uit order_items tabel
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('product_name');
        });
    }
};