<?php

/**
 * Bestandsnaam: SearchController.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.3
 * Datum: 2025-05-27
 * Tijd: 13:04:02
 * Doel: Controller voor zoekfunctionaliteit in de webshop. Behandelt product zoeken, live search suggestions en populaire producten weergave voor een optimale gebruikerservaring.
 */

// /app/Http/Controllers/SearchController.php
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SearchController extends Controller
{
    /**
     * Toon zoekresultaten pagina met gevonden producten
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        // Haal zoekterm op uit query parameter
        $query = $request->get('q', '');
        $products = collect();

        // Zoek alleen als minimaal 2 karakters zijn ingevoerd (performance en relevantie)
        if (strlen($query) >= 2) {
            $products = Product::with(['subcategory.category'])
                ->where('name', 'LIKE', "%{$query}%") // Zoek alleen in productnaam veld
                ->orderBy('name') // Sorteer alfabetisch voor consistente resultaten
                ->get()
                ->map(function ($product) {
                    // Transformeer product data voor frontend weergave
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'short_description' => $product->short_description,
                        'full_description' => $product->full_description,
                        'price' => (float) $product->price,
                        'image_path' => $product->image_path,
                        'subcategory' => [
                            'id' => $product->subcategory->id,
                            'name' => $product->subcategory->name,
                            'category' => [
                                'id' => $product->subcategory->category->id,
                                'name' => $product->subcategory->category->name,
                                'banner_image' => $product->subcategory->category->banner_image ?? 'default-banner.jpg'
                            ]
                        ]
                    ];
                });
        }

        return Inertia::render('SearchPage', [
            'query' => $query,
            'products' => $products,
            'resultsCount' => $products->count() // Aangepast om overeen te komen met Vue component
        ]);
    }

    /**
     * API endpoint voor live search suggesties tijdens het typen
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function suggestions(Request $request)
    {
        // Haal zoekterm op uit query parameter
        $query = $request->get('q', '');

        // Return lege array als minder dan 2 karakters (performance optimalisatie)
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        // Zoek producten voor live suggestions (beperkt aantal voor snelheid)
        $products = Product::with(['subcategory.category'])
            ->where('name', 'LIKE', "%{$query}%")
            ->orderBy('name')
            ->limit(8) // Limiteer tot 8 resultaten voor performance en UX
            ->get()
            ->map(function ($product) {
                // Compacte data voor snelle suggestions
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'image_path' => $product->image_path,
                    'category_name' => $product->subcategory->category->name,
                    'subcategory_name' => $product->subcategory->name,
                ];
            });

        return response()->json($products);
    }

    /**
     * Haal populaire/trending producten op voor zoeksuggesties
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function popular(Request $request)
    {
        // Haal populaire producten op (kan later uitgebreid worden met verkoopcijfers)
        $products = Product::with(['subcategory.category'])
            ->orderBy('name') // TODO: Vervang met sortering op populariteit/verkopen
            ->limit(6) // Limiteer tot 6 populaire items
            ->get()
            ->map(function ($product) {
                // Compacte data voor populaire producten weergave
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'image_path' => $product->image_path,
                    'category_name' => $product->subcategory->category->name,
                    'subcategory_name' => $product->subcategory->name,
                ];
            });

        return response()->json($products);
    }
}