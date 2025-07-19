<?php

/**
 * Bestandsnaam: 2025_01_04_112516_create_permission_tables.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-01-06
 * Tijd: 16:50:25
 * Doel: Database migration voor Spatie Permission package. Maakt alle benodigde tabellen aan voor uitgebreid rol- en permissie-beheer, inclusief many-to-many relaties, team ondersteuning en polymorphe model koppeling.
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Voer de database migrations uit
     * Maakt alle Spatie Permission tabellen aan voor rol- en permissie-beheer
     * 
     * @return void
     */
    public function up(): void
    {
        // Lees configuratie uit voor teams, tabelnamen en kolomnamen
        $teams = config('permission.teams');                    // Team functionaliteit aan/uit
        $tableNames = config('permission.table_names');         // Configureerbare tabelnamen
        $columnNames = config('permission.column_names');       // Configureerbare kolomnamen
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        // Valideer of permission configuratie correct geladen is
        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }
        
        // Valideer team configuratie als teams functionaliteit ingeschakeld is
        if ($teams && empty($columnNames['team_foreign_key'] ?? null)) {
            throw new \Exception('Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        // Maak permissions tabel aan voor alle systeem permissies
        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            //$table->engine('InnoDB');  // Database engine specificatie (optioneel)
            $table->bigIncrements('id');     // Auto-increment primary key voor permissie ID
            $table->string('name');          // Permissie naam (bijv. 'edit-users', 'delete-products')
            $table->string('guard_name');    // Laravel guard naam (bijv. 'web', 'api')
            $table->timestamps();            // created_at en updated_at timestamps

            // Unieke constraint op naam en guard combinatie
            $table->unique(['name', 'guard_name']);
        });

        // Maak roles tabel aan voor alle systeem rollen
        Schema::create($tableNames['roles'], function (Blueprint $table) use ($teams, $columnNames) {
            //$table->engine('InnoDB');  // Database engine specificatie (optioneel)
            $table->bigIncrements('id');     // Auto-increment primary key voor rol ID
            
            // Team ondersteuning (optioneel) - voor multi-tenant applicaties
            if ($teams || config('permission.testing')) { // permission.testing is fix voor sqlite testing
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }
            
            $table->string('name');          // Rol naam (bijv. 'admin', 'editor', 'customer')
            $table->string('guard_name');    // Laravel guard naam
            $table->timestamps();            // created_at en updated_at timestamps
            
            // Unieke constraint afhankelijk van team ondersteuning
            if ($teams || config('permission.testing')) {
                // Met teams: uniek per team, naam en guard
                $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name']);
            } else {
                // Zonder teams: uniek per naam en guard
                $table->unique(['name', 'guard_name']);
            }
        });

        // Maak model_has_permissions tabel aan voor directe permissies aan gebruikers
        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission, $teams) {
            $table->unsignedBigInteger($pivotPermission);  // Foreign key naar permissions tabel
            
            // Polymorphe relatie kolommen (kan User, maar ook andere models zijn)
            $table->string('model_type');                  // Class naam van het model (bijv. 'App\Models\User')
            $table->unsignedBigInteger($columnNames['model_morph_key']); // Model ID
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');
            
            // Foreign key constraint naar permissions tabel
            $table->foreign($pivotPermission)
                ->references('id')           // Referentie naar permissions.id
                ->on($tableNames['permissions'])
                ->onDelete('cascade');       // Verwijder model permissies als permissie wordt verwijderd
            
            // Team ondersteuning voor multi-tenant permissies
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');
                
                // Composite primary key met team ondersteuning
                $table->primary([$columnNames['team_foreign_key'], $pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            } else {
                // Composite primary key zonder team ondersteuning
                $table->primary([$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            }
        });

        // Maak model_has_roles tabel aan voor rol toewijzing aan gebruikers
        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole, $teams) {
            $table->unsignedBigInteger($pivotRole);       // Foreign key naar roles tabel
            
            // Polymorphe relatie kolommen (kan User, maar ook andere models zijn)
            $table->string('model_type');                 // Class naam van het model
            $table->unsignedBigInteger($columnNames['model_morph_key']); // Model ID
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');
            
            // Foreign key constraint naar roles tabel
            $table->foreign($pivotRole)
                ->references('id')           // Referentie naar roles.id
                ->on($tableNames['roles'])
                ->onDelete('cascade');       // Verwijder model rollen als rol wordt verwijderd
            
            // Team ondersteuning voor multi-tenant rollen
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');
                
                // Composite primary key met team ondersteuning
                $table->primary([$columnNames['team_foreign_key'], $pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            } else {
                // Composite primary key zonder team ondersteuning
                $table->primary([$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            }
        });

        // Maak role_has_permissions tabel aan voor permissie toewijzing aan rollen
        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission) {
            $table->unsignedBigInteger($pivotPermission); // Foreign key naar permissions tabel
            $table->unsignedBigInteger($pivotRole);       // Foreign key naar roles tabel
            
            // Foreign key constraint naar permissions tabel
            $table->foreign($pivotPermission)
                ->references('id')           // Referentie naar permissions.id
                ->on($tableNames['permissions'])
                ->onDelete('cascade');       // Verwijder rol permissies als permissie wordt verwijderd
            
            // Foreign key constraint naar roles tabel
            $table->foreign($pivotRole)
                ->references('id')           // Referentie naar roles.id
                ->on($tableNames['roles'])
                ->onDelete('cascade');       // Verwijder rol permissies als rol wordt verwijderd
            
            // Composite primary key voor unieke permissie-rol combinaties
            $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });

        // Leeg permission cache na database wijzigingen
        // Dit zorgt ervoor dat cached permissies opnieuw geladen worden
        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /**
     * Draai de migrations terug
     * Verwijdert alle Spatie Permission tabellen in de juiste volgorde
     * 
     * @return void
     */
    public function down(): void
    {
        // Haal tabelnamen op uit configuratie
        $tableNames = config('permission.table_names');

        // Valideer configuratie beschikbaarheid
        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');
        }

        // Verwijder tabellen in omgekeerde volgorde om foreign key problemen te voorkomen
        Schema::drop($tableNames['role_has_permissions']);    // Eerst pivot tabellen
        Schema::drop($tableNames['model_has_roles']);         // Dan model relatie tabellen
        Schema::drop($tableNames['model_has_permissions']);   // En model permissie tabellen
        Schema::drop($tableNames['roles']);                   // Dan roles tabel
        Schema::drop($tableNames['permissions']);             // Als laatste permissions tabel
    }
};