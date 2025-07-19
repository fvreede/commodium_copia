<?php

/**
 * Bestandsnaam: CategoryController.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-01-31
 * Tijd: 01:56:39
 * Doel: Deze controller beheert categorieën binnen het editor panel. Editors kunnen 
 *       categorieën aanmaken, bewerken en verwijderen inclusief banner beheer.
 */

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Toont een overzicht van alle categorieën met subcategorieën
     * Laadt gerelateerde subcategorieën voor volledige weergave van de structuur
     * 
     * @return \Inertia\Response
     */
    public function index()
    {
        // Haal categorieën op inclusief bijbehorende subcategorieën
        $categories = Category::with('subcategories')->get();

        return Inertia::render('Editor/Categories/Index', [
            'categories' => $categories
        ]);
    }

    /**
     * Toont het formulier voor het aanmaken van een nieuwe categorie
     * 
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Editor/Categories/Create');
    }

    /**
     * Slaat een nieuwe categorie op in de database
     * Verwerkt de geüploade banner afbeelding en slaat deze op
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Valideer invoergegevens inclusief verplichte banner
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'banner' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Sla banner op in storage
        $path = $request->file('banner')->store('images/subcategories/banners', 'public');

        // Maak nieuwe categorie aan met banner
        Category::create([
            'name' => $validated['name'],
            'banner_path' => $path
        ]);

        return redirect()->route('editor.categories.index');
    }

    /**
     * Werkt een bestaande categorie bij
     * Optioneel vervangen van banner afbeelding
     * 
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Category $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Category $category)
    {
        // Valideer invoergegevens (banner is optioneel bij update)
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Vervang banner indien nieuwe afbeelding is geüpload
        if ($request->hasFile('banner')) {
            // Verwijder oude banner uit storage
            Storage::disk('public')->delete($category->banner_path);
            
            // Sla nieuwe banner op
            $path = $request->file('banner')->store('images/subcategories/banners', 'public');
            $category->banner_path = $path;
        }

        // Werk categorie naam bij
        $category->name = $validated['name'];
        $category->save();

        return redirect()->route('editor.categories.index');
    }

    /**
     * Verwijdert een categorie uit de database
     * Ruimt ook de bijbehorende banner afbeelding op
     * 
     * @param \App\Models\Category $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Category $category)
    {
        // Verwijder banner bestand uit storage indien aanwezig
        if ($category->banner_path) {
            Storage::disk('public')->delete($category->banner_path);
        }

        // Verwijder categorie uit database
        $category->delete();

        return redirect()->route('editor.categories.index');
    }
}