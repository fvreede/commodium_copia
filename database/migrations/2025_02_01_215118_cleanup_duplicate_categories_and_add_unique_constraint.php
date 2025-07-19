<?php

/**
 * Bestandsnaam: 2025_02_01_215118_cleanup_duplicate_categories_and_add_unique_constraint.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-02-02
 * Tijd: 00:33:31
 * Doel: Database migration voor het opruimen van duplicate categorieën en toevoegen van unique constraint. Consolideert duplicate categorie namen door subcategorieën te verplaatsen naar de oudste categorie en duplicaten te verwijderen, waarna unique constraint wordt toegevoegd.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Voer de database migrations uit
     * Ruimt duplicate categorieën op en voegt unique constraint toe aan name kolom
     * 
     * @return void
     */
    public function up()
    {
        // Stap 1: Behoud alleen de vroegste entry voor elke categorie naam en
        // verplaats alle subcategorieën naar die entry
        
        // Zoek alle categorie namen die duplicaten hebben
        $duplicates = DB::table('categories')
            ->select('name')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')  // Alleen namen die meer dan 1 keer voorkomen
            ->get();

        // Verwerk elke duplicate categorie naam
        foreach ($duplicates as $duplicate) {
            // Haal alle IDs op voor deze categorie naam, gesorteerd op aanmaakdatum
            $categoryIds = DB::table('categories')
                ->where('name', $duplicate->name)
                ->orderBy('created_at')      // Oudste eerst (chronologische volgorde)
                ->pluck('id')
                ->toArray();

            // Eerste ID is degene die we behouden (oudste record)
            $keepId = array_shift($categoryIds);

            // Verplaats alle subcategorieën van duplicate categorieën naar de categorie die we behouden
            // Dit voorkomt verlies van subcategorie data bij het verwijderen van duplicaten
            DB::table('subcategories')
                ->whereIn('category_id', $categoryIds)  // Alle subcategorieën van duplicate categorieën
                ->update(['category_id' => $keepId]);   // Verplaats naar categorie die blijft bestaan

            // Verwijder de duplicate categorieën (behalve degene die we behouden)
            DB::table('categories')
                ->whereIn('id', $categoryIds)
                ->delete();
        }

        // Stap 2: Voeg unique constraint toe aan name kolom
        // Nu alle duplicaten zijn opgeruimd, kan de unique constraint veilig toegevoegd worden
        Schema::table('categories', function (Blueprint $table) {
            $table->unique('name');  // Zorg ervoor dat categorie namen uniek zijn
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
        // Verwijder unique constraint van name kolom
        // LET OP: De data consolidatie (verwijderde duplicaten) kan niet ongedaan gemaakt worden
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
    }
};