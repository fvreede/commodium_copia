<?php

/**
 * Bestandsnaam: CategoryStructureController.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.3
 * Datum: 2025-05-22
 * Tijd: 13:57:04
 * Doel: Deze controller beheert de CRUD-operaties voor categorieën in het admin panel. 
 *       Inclusief het uploaden en verwerken van afbeeldingen met crop functionaliteit.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Spatie\Image\Image;

class CategoryStructureController extends Controller
{
    /**
     * Toont een overzicht van alle categorieën
     * Haalt categorieën op inclusief het aantal subcategorieën (zonder verwijderde items)
     * 
     * @return \Inertia\Response
     */
    public function index()
    {
        // Haal categorieën op met telling van actieve subcategorieën
        $categories = Category::withCount(['subcategories' => function($query) {
            $query->withoutTrashed();
        }])->withoutTrashed()->get();

        return Inertia::render('Admin/Categories/Index', [
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
        return Inertia::render('Admin/Categories/Create');
    }

    /**
     * Slaat een nieuwe categorie op in de database
     * Verwerkt geüploade afbeelding met crop functionaliteit
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Valideer de invoergegevens
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')],
            'description' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'imagePosition.x' => 'required|numeric',
            'imagePosition.y' => 'required|numeric'
        ]);
        
        // Verwerk de geüploade afbeelding
        if ($request->hasFile('image')) {
            // Genereer unieke bestandsnaam
            $filename = time() . '_' . uniqid() . '.jpg';
            $path = 'images/categories/'. $filename;
            $fullPath = Storage::disk('public')->path($path);

            // Laad afbeelding, crop naar 1000x1000 pixels en optimaliseer
            Image::load($request->file('image'))
                ->manualCrop(1000, 1000, abs($validated['imagePosition']['x']), abs($validated['imagePosition']['y']))
                ->optimize()
                ->save($fullPath);

            // Maak nieuwe categorie aan in database
            Category::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'image_path' => $path,
                'banner_path' => null // Dit wordt beheerd door editors
            ]);

            return redirect()->route('admin.categories.index');
        }

        // Foutmelding als afbeelding niet verwerkt kon worden
        return back()->withErrors(['image' => 'Error processing image']);
    }

    /**
     * Toont het formulier voor het bewerken van een bestaande categorie
     * Inclusief debug informatie over de afbeelding
     * 
     * @param \App\Models\Category $category
     * @return \Inertia\Response
     */
    public function edit(Category $category)
    {
        // Bepaal volledig pad naar de afbeelding voor debug doeleinden
        $imagePath = storage_path('app/public/' . $category->image_path);
        
        // Log debug informatie over de afbeelding
        Log::info('Category image debug', [
            'database_path' => $category->image_path,
            'full_storage_path' => $imagePath,
            'exists' => file_exists($imagePath),
            'storage_files' => scandir(storage_path('app/public/images/categories'))
        ]);

        return Inertia::render('Admin/Categories/Edit', [
            'category' => array_merge($category->toArray(), [
                '_debug' => [
                    'image_exists' => file_exists($imagePath)
                ]
            ])
        ]);
    }

    /**
     * Werkt een bestaande categorie bij
     * Vervangt optioneel de afbeelding met een nieuwe gecroppte versie
     * 
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Category $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Category $category)
    {
        // Valideer invoergegevens (afbeelding is optioneel bij update)
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->ignore($category->id)],
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'imagePosition.x' => 'nullable|required_with:image|numeric',
            'imagePosition.y' => 'nullable|required_with:image|numeric'
        ]);

        // Verwerk nieuwe afbeelding indien aanwezig
        if ($request->hasFile('image')) {
            // Verwijder oude afbeelding uit storage
            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }

            // Genereer nieuwe unieke bestandsnaam
            $filename = time() . '_' . uniqid() . '.jpg';
            $path = 'images/categories/'. $filename;
            $fullPath = Storage::disk('public')->path($path);

            // Verwerk nieuwe afbeelding met crop en optimalisatie
            Image::load($request->file('image'))
                ->manualCrop(1000, 1000, abs($validated['imagePosition']['x']), abs($validated['imagePosition']['y']))
                ->optimize()
                ->save($fullPath);

            // Voeg nieuwe afbeelding pad toe aan validated data
            $validated['image_path'] = $path;
        }

        // Werk categorie bij in database
        $category->update($validated);

        return redirect()->route('admin.categories.index');
    }

    /**
     * Verwijdert een categorie uit de database
     * Controleert eerst of er geen actieve subcategorieën zijn
     * 
     * @param \App\Models\Category $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Category $category)
    {
        // Controleer of categorie actieve subcategorieën heeft
        if ($category->subcategories()->withoutTrashed()->exists()) {
            return back()->withErrors([
                'error' => 'Cannot delete category that has active subcategories.'
            ]);
        }

        // Verwijder categorie (soft delete)
        $category->delete();
        return redirect()->route('admin.categories.index');
    }
}