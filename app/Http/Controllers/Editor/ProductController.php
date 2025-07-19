<?php

/**
 * Bestandsnaam: ProductController.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.6
 * Datum: 2025-06-07
 * Tijd: 21:50:58
 * Doel: Controller voor het beheren van producten in de editor. Behandelt CRUD operaties voor producten inclusief afbeelding upload en categorisatie.
 */

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\Category;

class ProductController extends Controller
{
    /**
     * Toon overzicht van alle producten
     * 
     * @return \Inertia\Response
     */
    public function index()
    {
        // Haal alle producten op met bijbehorende subcategorie relaties
        $products = Product::with('subcategory')->get();
        $subcategories = Subcategory::all();
        
        return Inertia::render('Editor/Products/Index', [
            'products' => $products,
            'subcategories' => $subcategories
        ]);
    }

    /**
     * Toon formulier voor het aanmaken van een nieuw product
     * 
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Editor/Products/Create', [
            'categories' => Category::all(),
            'subcategories' => Subcategory::with('category')->get()
        ]);
    }

    /**
     * Sla een nieuw product op in de database
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Valideer de inkomende gegevens
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'required|string',
            'full_description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:1',
            'subcategory_id' => 'required|exists:subcategories,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Haal subcategorie en bijbehorende categorie op voor mapstructuur
        $subcategory = Subcategory::with('category')->find($validated['subcategory_id']);
        $categoryName = strtolower(str_replace(' ', '_', $subcategory->category->name));
        $subcategoryName = strtolower(str_replace(' ', '_', $subcategory->name));
        
        // Sla afbeelding op in georganiseerde mapstructuur
        $path = $request->file('image')->store("images/products/{$categoryName}/{$subcategoryName}", 'public');

        // Maak het nieuwe product aan
        Product::create([
            'name' => $validated['name'],
            'short_description' => $validated['short_description'],
            'full_description' => $validated['full_description'],
            'price' => $validated['price'],
            'stock_quantity' => $validated['stock_quantity'],
            'subcategory_id' => $validated['subcategory_id'],
            'image_path' => $path
        ]);

        return redirect()->route('editor.products.index');
    }

    /**
     * Toon een specifiek product met alle details
     * 
     * @param \App\Models\Product $product
     * @return \Inertia\Response
     */
    public function show(Product $product)
    {
        // Laad het product met alle benodigde relaties
        $product->load(['subcategory.category']);
        
        // Organiseer productgegevens voor frontend
        $productData = [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->short_description,
            'fullDescription' => $product->full_description,
            'price' => (float) $product->price, // Zorg ervoor dat het een float is
            'imageSrc' => $product->image_path,
            'stock_quantity' => $product->stock_quantity,
            'subcategory' => [
                'id' => $product->subcategory->id,
                'name' => $product->subcategory->name,
                'category' => [
                    'id' => $product->subcategory->category->id,
                    'name' => $product->subcategory->category->name,
                    'banner_path' => $product->subcategory->category->banner_path,
                ]
            ]
        ];

        return Inertia::render('ProductPage', [
            'id' => (string) $product->id,
            'product' => $productData,
            'bannerSrc' => $product->subcategory->category->banner_path ?? 'default-banner.jpg',
            'categoryName' => $product->subcategory->category->name,
            'subcategoryName' => $product->subcategory->name,
            'categoryId' => (string) $product->subcategory->category->id,
        ]);
    }

    /**
     * Toon formulier voor het bewerken van een bestaand product
     * 
     * @param \App\Models\Product $product
     * @return \Inertia\Response
     */
    public function edit(Product $product)
    {
        return Inertia::render('Editor/Products/Edit', [
        'product' => $product,
        'categories' => Category::all(),
        'subcategories' => Subcategory::with('category')->get()
        ]);
    }

    /**
     * Werk een bestaand product bij in de database
     * 
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Product $product)
    {
        // Valideer de inkomende gegevens (afbeelding is optioneel bij update)
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'required|string',
            'full_description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:1',
            'subcategory_id' => 'required|exists:subcategories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Verwerk nieuwe afbeelding als deze is geüpload
        if ($request->hasFile('image')) {
            // Verwijder oude afbeelding
            Storage::disk('public')->delete($product->image_path);
            
            // Bepaal nieuwe mapstructuur op basis van subcategorie
            $subcategory = Subcategory::with('category')->find($validated['subcategory_id']);
            $categoryName = strtolower(str_replace(' ', '_', $subcategory->category->name));
            $subcategoryName = strtolower(str_replace(' ', '_', $subcategory->name));
            
            // Sla nieuwe afbeelding op
            $path = $request->file('image')->store("images/products/{$categoryName}/{$subcategoryName}", 'public');
            $product->image_path = $path;
        }

        // Werk het product bij met nieuwe gegevens
        $product->update($validated);
        return redirect()->route('editor.products.index');
    }

    /**
     * Verwijder een product uit de database
     * 
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Product $product)
    {
        // Verwijder bijbehorende afbeelding van storage
        Storage::disk('public')->delete($product->image_path);
        
        // Verwijder het product uit de database
        $product->delete();
        
        return redirect()->route('editor.products.index');
    }
}