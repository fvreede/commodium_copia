<?php

/**
 * Bestandsnaam: 2025_05_30_184014_enhance_products_table.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-05-30
 * Tijd: 20:42:25
 * Doel: Database migration voor het toevoegen van voorraad beheer en product status velden aan products tabel. Enables inventory tracking met stock_quantity en product zichtbaarheid controle met is_active voor complete e-commerce catalog management.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voer de database migrations uit
     * Voegt stock_quantity en is_active kolommen toe aan products tabel voor inventory en status beheer
     * 
     * @return void
     */
    public function up(): void
    {
        // Wijzig products tabel om voorraad en status management velden toe te voegen
        Schema::table('products', function (Blueprint $table) {
            
            // Voeg stock_quantity kolom toe als deze nog niet bestaat
            if (!Schema::hasColumn('products', 'stock_quantity')) {
                // Integer kolom voor voorraad tracking
                $table->integer('stock_quantity')->default(0);
                
                // Default waarde 0 omdat:
                // - Nieuwe producten starten zonder voorraad (veilig)
                // - Bestaande producten krijgen voorraad 0 (moet handmatig bijgewerkt worden)
                // - Voorkomt accidentele verkoop van niet-beschikbare producten
                // - Stock moet bewust ingesteld worden door admin/inventory management
            }
            
            // Voeg is_active kolom toe als deze nog niet bestaat
            if (!Schema::hasColumn('products', 'is_active')) {
                // Boolean kolom voor product zichtbaarheid en beschikbaarheid
                $table->boolean('is_active')->default(true);
                
                // Default waarde true omdat:
                // - Bestaande producten blijven zichtbaar na migration
                // - Voorkomt plotselinge verdwijning van producten uit catalog
                // - Nieuwe producten zijn standaard actief (kan handmatig uitgeschakeld)
                // - Backwards compatibility met bestaande product catalog
            }
            
            // Gebruik cases voor deze velden:
            // stock_quantity: 
            // - Voorraad tracking en low-stock alerts
            // - Voorkom overselling via cart validatie
            // - Automatische "uitverkocht" status weergave
            // - Inventory management en reporting
            
            // is_active:
            // - Tijdelijk producten verbergen zonder verwijderen
            // - Seizoensproducten activeren/deactiveren
            // - Discontinued producten behouden voor order history
            // - A/B testing van product zichtbaarheid
        });
    }

    /**
     * Draai de migrations terug
     * Verwijdert de stock_quantity en is_active kolommen uit products tabel
     * 
     * @return void
     */
    public function down(): void
    {
        // Verwijder stock_quantity en is_active kolommen uit products tabel
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['stock_quantity', 'is_active']);
        });
    }
};