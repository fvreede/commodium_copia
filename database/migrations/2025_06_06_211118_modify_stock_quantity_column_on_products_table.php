<?php

/**
 * Bestandsnaam: 2025_06_06_211118_modify_stock_quantity_column_on_products_table.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-06-07
 * Tijd: 00:00:09
 * Doel: Database migration voor het wijzigen van de default waarde van stock_quantity in products tabel. Verandert default van 0 naar null om onderscheid te maken tussen expliciet geen voorraad (0) en onbeperkte/niet-getrackte voorraad (null).
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voer de database migrations uit
     * Wijzigt de default waarde van stock_quantity van 0 naar null voor flexibeler voorraad beheer
     * 
     * @return void
     */
    public function up(): void
    {
        // Wijzig stock_quantity kolom om default waarde van 0 naar null te veranderen
        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock_quantity')->default(null)->change();
            
            // Waarom deze wijziging belangrijk is:
            // 
            // OUDE SITUATIE (default 0):
            // - Alle nieuwe producten krijgen automatisch voorraad 0
            // - Geen onderscheid tussen "uitverkocht" en "voorraad niet getrackt"
            // - Kan leiden tot ongewenste "uitverkocht" status voor nieuwe producten
            // 
            // NIEUWE SITUATIE (default null):
            // - Null = Voorraad wordt niet getrackt (onbeperkt beschikbaar)
            // - 0 = Expliciet uitverkocht/geen voorraad
            // - Positieve waarde = Exacte voorraad beschikbaar
            // 
            // Use cases voor null voorraad:
            // - Digitale producten (onbeperkt verkochbaar)
            // - Service producten (geen fysieke voorraad)
            // - Made-to-order producten
            // - Producten waar voorraad extern getrackt wordt
            // 
            // Business logic wijzigingen:
            // - Cart validatie: null = altijd toevoegbaar
            // - Frontend: null = "op voorraad" (geen voorraad indicator)
            // - Admin: null = voorraad tracking uitgeschakeld
        });
    }

    /**
     * Draai de migrations terug
     * Herstelt de default waarde van stock_quantity naar 0
     * 
     * @return void
     */
    public function down(): void
    {
        // Herstel stock_quantity default waarde naar 0
        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock_quantity')->default(0)->change();
        });
    }
};