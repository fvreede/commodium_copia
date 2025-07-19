<?php

/**
 * Bestandsnaam: SubcategoryController.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-01-31
 * Tijd: 01:56:39
 * Doel: Controller voor het beheren van subcategorieën in de editor. Behandelt CRUD operaties voor subcategorieën en hun relaties met hoofdcategorieën en producten.
 */

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubcategoryController extends Controller
{
    /**
     * Toon overzicht van alle subcategorieën
     * 
     * @return \Inertia\Response
     */
    public function index()
    {
        // Haal alle subcategorieën op met bijbehorende categorieën en producten
        $subcategories = Subcategory::with(['category', 'products'])->get();
        $categories = Category::all();
        
        return Inertia::render('Editor/Subcategories/Index', [
            'subcategories' => $subcategories,
            'categories' => $categories
        ]);
    }

    /**
     * Sla een nieuwe subcategorie op in de database
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Valideer de inkomende gegevens
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id'
        ]);

        // Maak de nieuwe subcategorie aan
        Subcategory::create($validated);
        
        return redirect()->route('editor.subcategories.index');
    }

    /**
     * Werk een bestaande subcategorie bij in de database
     * 
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Subcategory $subcategory
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Subcategory $subcategory)
    {
        // Valideer de inkomende gegevens
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id'
        ]);

        // Werk de subcategorie bij met nieuwe gegevens
        $subcategory->update($validated);
        
        return redirect()->route('editor.subcategories.index');
    }

    /**
     * Verwijder een subcategorie uit de database
     * 
     * @param \App\Models\Subcategory $subcategory
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Subcategory $subcategory)
    {
        // Verwijder de subcategorie uit de database
        // Note: Gekoppelde producten worden automatisch behandeld door database constraints
        $subcategory->delete();
        
        return redirect()->route('editor.subcategories.index');
    }
}