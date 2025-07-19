<?php

/**
 * Bestandsnaam: BannerController.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-01-30
 * Tijd: 20:50:14
 * Doel: Deze controller beheert banner afbeeldingen voor categorieën binnen het editor panel.
 *       Editors kunnen banners uploaden en vervangen voor visuele weergave van categorieën.
 */

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class BannerController extends Controller
{
    /**
     * Toont een overzicht van alle categorieën voor banner beheer
     * Geeft editors toegang tot alle categorieën waar banners voor beheerd kunnen worden
     * 
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('Editor/Banners/Index', [
            'categories' => Category::all()
        ]);
    }

    /**
     * Toont het bewerkingsformulier voor een specifieke categorie banner
     * 
     * @param \App\Models\Category $category
     * @return \Inertia\Response
     */
    public function edit(Category $category)
    {
        return Inertia::render('Editor/Banners/Edit', [
            'category' => $category,
        ]);
    }

    /**
     * Werkt de banner van een categorie bij
     * Vervangt de bestaande banner met een nieuw geüploade afbeelding
     * 
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Category $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Category $category)
    {
        // Valideer de geüploade banner afbeelding
        $request->validate([
            'banner' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Verwijder oude banner indien aanwezig
        if ($category->banner_path) {
            Storage::disk('public')->delete($category->banner_path);
        }

        // Sla nieuwe banner op in storage
        $path = $request->file('banner')->store('images/subcategories/banners', 'public');

        // Werk categorie bij met nieuwe banner pad
        $category->update([
            'banner_path' => $path,
        ]);

        return redirect()->route('editor.banners.index');
    }
}