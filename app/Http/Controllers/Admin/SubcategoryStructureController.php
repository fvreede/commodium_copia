<?php

/**
 * Bestandsnaam: SubcategoryStructureController.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.2
 * Datum: 2025-02-02
 * Tijd: 00:33:31
 * Doel: Deze controller beheert de CRUD-operaties voor subcategorieën in het admin panel.
 *       Subcategorieën zijn gekoppeld aan hoofdcategorieën en bevatten producten.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SubcategoryStructureController extends Controller
{
    /**
     * Toont een overzicht van alle subcategorieën
     * Haalt subcategorieën op met bijbehorende categorieën en product tellingen
     * 
     * @return \Inertia\Response
     */
    public function index()
    {
        // Haal subcategorieën op met gerelateerde categorie en aantal producten
        $subcategories = Subcategory::with(['category' => function($query) {
            $query->withoutTrashed();
        }])
        ->withCount(['products' => function($query) {
            $query->withoutTrashed();
        }])
        ->withoutTrashed()
        ->get();

        // Haal alle actieve categorieën op voor filtering/display
        $categories = Category::withoutTrashed()->get();

        return Inertia::render('Admin/Subcategories/Index', [
            'subcategories' => $subcategories,
            'categories' => $categories
        ]);
    }

    /**
     * Toont het formulier voor het aanmaken van een nieuwe subcategorie
     * 
     * @return \Inertia\Response
     */
    public function create()
    {
        // Haal alle actieve categorieën op voor dropdown selectie
        $categories = Category::withoutTrashed()->get();

        return Inertia::render('Admin/Subcategories/Create', [
            'categories' => $categories
        ]);
    }

    /**
     * Slaat een nieuwe subcategorie op in de database
     * Valideert dat de naam uniek is binnen de geselecteerde categorie
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Valideer invoergegevens met unieke naam per categorie
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('subcategories')
                    ->where('category_id', $request->category_id)
            ],
            'category_id' => ['required', 'exists:categories,id,deleted_at,NULL']
        ]);

        // Maak nieuwe subcategorie aan
        Subcategory::create($validated);

        return redirect()->route('admin.subcategories.index');
    }

    /**
     * Toont het formulier voor het bewerken van een bestaande subcategorie
     * 
     * @param \App\Models\Subcategory $subcategory
     * @return \Inertia\Response
     */
    public function edit(Subcategory $subcategory)
    {
        // Haal alle actieve categorieën op voor dropdown selectie
        $categories = Category::withoutTrashed()->get();

        return Inertia::render('Admin/Subcategories/Edit', [
            'subcategory' => $subcategory,
            'categories' => $categories
        ]);
    }

    /**
     * Werkt een bestaande subcategorie bij
     * Valideert unieke naam binnen categorie, exclusief huidige subcategorie
     * 
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Subcategory $subcategory
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Subcategory $subcategory)
    {
        // Valideer invoergegevens, negeer huidige subcategorie bij unieke controle
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('subcategories')
                    ->where('category_id', $request->category_id)
                    ->ignore($subcategory->id)
            ],
            'category_id' => ['required', 'exists:categories,id,deleted_at,NULL']
        ]);

        // Werk subcategorie bij in database
        $subcategory->update($validated);

        return redirect()->route('admin.subcategories.index');
    }

    /**
     * Verwijdert een subcategorie uit de database
     * Controleert eerst of er geen actieve producten zijn gekoppeld
     * 
     * @param \App\Models\Subcategory $subcategory
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Subcategory $subcategory)
    {
        // Controleer of subcategorie actieve producten heeft
        if ($subcategory->products()->withoutTrashed()->exists()) {
            return back()->withErrors([
                'error' => 'Cannot delete subcategory that has active products.'
            ]);
        }

        // Verwijder subcategorie (soft delete)
        $subcategory->delete();
        
        return redirect()->route('admin.subcategories.index');
    }
}