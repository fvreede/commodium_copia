<?php

/**
 * Bestandsnaam: 2025_02_01_231248_cleanup_duplicate_subcategories_and_add_unique_constraint.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-02-02
 * Tijd: 00:33:31
 * Doel: Database migration voor het opruimen van duplicate subcategorieën en toevoegen van unique constraint. Consolideert duplicate subcategorie namen binnen dezelfde hoofdcategorie door producten te verplaatsen naar de oudste subcategorie en duplicaten te verwijderen, waarna unique constraint wordt toegevoegd.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Voer de database migrations uit
     * Ruimt duplicate subcategorieën op en voegt unique constraint toe aan name+category_id combinatie
     * 
     * @return void
     */
    public function up()
    {
        // Stap 1: Zoek en ruim duplicate subcategorie namen binnen dezelfde hoofdcategorie op
        
        // Zoek alle subcategorie naam/category_id combinaties die duplicaten hebben
        $duplicates = DB::table('subcategories')
            ->select('name', 'category_id')
            ->groupBy('name', 'category_id')        // Groepeer per naam binnen elke category
            ->havingRaw('COUNT(*) > 1')             // Alleen combinaties die meer dan 1 keer voorkomen
            ->get();

        // Verwerk elke duplicate subcategorie naam/category combinatie
        foreach ($duplicates as $duplicate) {
            // Haal alle IDs op voor deze subcategorie naam binnen deze category, gesorteerd op aanmaakdatum
            $subcategoryIds = DB::table('subcategories')
                ->where('name', $duplicate->name)
                ->where('category_id', $duplicate->category_id)
                ->orderBy('created_at')              // Oudste eerst (chronologische volgorde)
                ->pluck('id')
                ->toArray();

            // Eerste ID is degene die we behouden (oudste record)
            $keepId = array_shift($subcategoryIds);

            // Verplaats alle producten van duplicate subcategorieën naar de subcategorie die we behouden
            // Dit voorkomt verlies van product data bij het verwijderen van duplicaten
            DB::table('products')
                ->whereIn('subcategory_id', $subcategoryIds)  // Alle producten van duplicate subcategorieën
                ->update(['subcategory_id' => $keepId]);      // Verplaats naar subcategorie die blijft bestaan

            // Verwijder de duplicate subcategorieën (behalve degene die we behouden)
            DB::table('subcategories')
                ->whereIn('id', $subcategoryIds)
                ->delete();
        }

        // Stap 2: Voeg unique constraint toe aan name+category_id combinatie
        // Nu alle duplicaten zijn opgeruimd, kan de unique constraint veilig toegevoegd worden
        Schema::table('subcategories', function (Blueprint $table) {
            // Composite unique constraint: subcategorie naam moet uniek zijn binnen elke hoofdcategorie
            // Dit betekent dat verschillende hoofdcategorieën wel dezelfde subcategorie namen kunnen hebben
            // Bijvoorbeeld: "Appels" kan bestaan in zowel "Fruit" als "Biologisch" hoofdcategorieën
            $table->unique(['name', 'category_id']);
        });
    }

    /**
     * Draai de migrations terug
     * Verwijdert de unique constraint (data consolidatie kan niet ongedaan gemaakt worden)
     * 
     * @return void
     */
    public function down()
    {
        // Verwijder unique constraint van name+category_id combinatie
        // LET OP: De data consolidatie (verwijderde duplicaten) kan niet ongedaan gemaakt worden
        Schema::table('subcategories', function (Blueprint $table) {
            $table->dropUnique(['name', 'category_id']);
        });
    }
};