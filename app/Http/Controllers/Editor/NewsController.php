<?php

/**
 * Bestandsnaam: NewsController.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-01-29
 * Tijd: 19:27:01
 * Doel: Deze controller beheert nieuwsartikelen binnen het editor panel. Editors kunnen 
 *       artikelen aanmaken, bewerken, publiceren en verwijderen inclusief afbeelding beheer.
 */

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class NewsController extends Controller
{
    /**
     * Toont een overzicht van alle nieuwsartikelen
     * Sorteert artikelen op meest recente eerst
     * 
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('Editor/News/Index', [
            'articles' => NewsArticle::latest()->get()
        ]);
    }

    /**
     * Toont het formulier voor het aanmaken van een nieuw artikel
     * 
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Editor/News/Create');
    }

    /**
     * Slaat een nieuw nieuwsartikel op in de database
     * Verwerkt afbeelding upload en bepaalt publicatie datum
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Valideer alle artikel gegevens inclusief afbeelding
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date'
        ]);

        // Sla artikel afbeelding op in storage
        $path = $request->file('image')->store('images/news', 'public');

        // Maak nieuw artikel aan met automatische publicatie datum indien gepubliceerd
        NewsArticle::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image_path' => $path,
            'is_published' => $validated['is_published'],
            'published_at' => $validated['is_published'] ? now() : $validated['published_at']
        ]);

        return redirect()->route('editor.news.index');
    }

    /**
     * Toont het bewerkingsformulier voor een specifiek artikel
     * 
     * @param \App\Models\NewsArticle $article
     * @return \Inertia\Response
     */
    public function edit(NewsArticle $article)
    {
        return Inertia::render('Editor/News/Edit', [
            'article' => $article
        ]);
    }

    /**
     * Werkt een bestaand nieuwsartikel bij
     * Optioneel vervangen van afbeelding en bijwerken van publicatie status
     * 
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\NewsArticle $article
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, NewsArticle $article)
    {
        // Valideer artikel gegevens (afbeelding is optioneel bij update)
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date'
        ]);

        // Vervang afbeelding indien nieuwe is geüpload
        if ($request->hasFile('image')) {
            // Verwijder oude afbeelding uit storage
            Storage::disk('public')->delete($article->image_path);
            
            // Sla nieuwe afbeelding op
            $path = $request->file('image')->store('images/news', 'public');
            $article->image_path = $path;
        }

        // Werk artikel bij met nieuwe gegevens en publicatie logica
        $article->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_published' => $validated['is_published'],
            'published_at' => $validated['is_published'] ? now() : $validated['published_at']
        ]);

        return redirect()->route('editor.news.index');
    }

    /**
     * Verwijdert een nieuwsartikel uit de database
     * Ruimt ook de bijbehorende afbeelding op uit storage
     * 
     * @param \App\Models\NewsArticle $article
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(NewsArticle $article)
    {
        // Verwijder artikel afbeelding uit storage
        Storage::disk('public')->delete($article->image_path);

        // Verwijder artikel uit database
        $article->delete();

        return redirect()->route('editor.news.index');
    }
}