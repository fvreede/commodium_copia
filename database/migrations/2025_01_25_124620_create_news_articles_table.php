<?php

/**
 * Bestandsnaam: 2025_01_25_124620_create_news_articles_table.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-05-22
 * Tijd: 13:57:04
 * Doel: Database migration voor het aanmaken van de news_articles tabel. Enables content management systeem voor nieuwsartikelen, blog posts en announcements met publicatie status en scheduling functionaliteit.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voer de database migrations uit
     * Maakt de news_articles tabel aan voor content management van nieuwsartikelen
     * 
     * @return void
     */
    public function up(): void
    {
        // Maak news_articles tabel aan voor website content management
        Schema::create('news_articles', function (Blueprint $table) {
            $table->id();                    // Auto-increment primary key voor unieke artikel identificatie
            $table->string('title');         // Titel van het nieuwsartikel voor weergave en SEO
            $table->text('content');         // Hoofdinhoud van het artikel (kan HTML markup bevatten)
            $table->string('image_path')->nullable(); // Pad naar uitgelichte afbeelding (optioneel)
            
            // Publicatie status management
            $table->boolean('is_published')->default(false); // Publicatie status (standaard concept/draft)
            $table->timestamp('published_at')->nullable();   // Geplande of werkelijke publicatie datum/tijd
            
            $table->softDeletes();           // deleted_at kolom voor soft delete (behoudt gepubliceerde content)
            $table->timestamps();            // created_at en updated_at voor artikel lifecycle tracking
            
            // Mogelijke uitbreidingen voor de toekomst:
            // - author_id foreign key naar users tabel
            // - excerpt kolom voor korte samenvatting
            // - slug kolom voor SEO-vriendelijke URLs
            // - meta_description voor SEO
            // - view_count voor populariteit tracking
        });
    }

    /**
     * Draai de migrations terug
     * Verwijdert de news_articles tabel en alle bijbehorende content
     * 
     * @return void
     */
    public function down(): void
    {
        // Verwijder news_articles tabel (gebruik dropIfExists voor veiligheid)
        Schema::dropIfExists('news_articles');
    }
};