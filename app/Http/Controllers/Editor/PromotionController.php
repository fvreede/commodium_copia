<?php

/**
 * Bestandsnaam: PromotionController.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-05-22
 * Tijd: 13:57:04
 * Doel: Controller voor het beheren van promoties in de editor. Behandelt CRUD operaties voor promoties inclusief gekoppelde producten met kortingsprijzen en afbeelding beheer.
 */

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PromotionController extends Controller
{
    /**
     * Toon overzicht van alle promoties
     * 
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('Editor/Promotions/Index', [
            'promotions' => Promotion::with('products')->get()
        ]);
    }

    /**
     * Toon formulier voor het aanmaken van een nieuwe promotie
     * 
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Editor/Promotions/Create', [
            'products' => Product::all()
        ]);
    }

    /**
     * Sla een nieuwe promotie op in de database
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Valideer de inkomende gegevens inclusief producten en kortingsprijzen
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'cta_text' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'valid_until' => 'required|date',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.discount_price' => 'required|numeric|min:0'
       ]);

       // Sla promotie afbeelding op in de juiste map
       $path = $request->file('image')->store('images/promotions', 'public');

       // Maak de nieuwe promotie aan
       $promotion = Promotion::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'cta_text' => $validated['cta_text'],
            'image_path' => $path,
            'valid_until' => $validated['valid_until'],
            'is_active' => true
       ]);

       // Koppel producten aan de promotie met hun kortingsprijzen
       foreach ($validated['products'] as $product) {
            $promotion->products()->attach($product['id'], [
                'discount_price' => $product['discount_price']
            ]);
        }

        return redirect()->route('editor.promotions.index');
    }

    /**
     * Toon formulier voor het bewerken van een bestaande promotie
     * 
     * @param \App\Models\Promotion $promotion
     * @return \Inertia\Response
     */
    public function edit(Promotion $promotion)
    {
        return Inertia::render('Editor/Promotions/Edit', [
            'promotion' => $promotion->load('products'),
            'products' => Product::all()
        ]);
    }

    /**
     * Werk een bestaande promotie bij in de database
     * 
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Promotion $promotion
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Promotion $promotion)
    {
        // Valideer de inkomende gegevens (afbeelding is optioneel bij update)
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'cta_text' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'valid_until' => 'required|date',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.discount_price' => 'required|numeric|min:0'
        ]);

        // Verwerk nieuwe afbeelding als deze is geüpload
        if ($request->hasFile('image')) {
            // Verwijder oude afbeelding
            Storage::disk('public')->delete($promotion->image_path);
            // Sla nieuwe afbeelding op
            $path = $request->file('image')->store('images/promotions', 'public');
            $promotion->image_path = $path;
        }

        // Werk de promotie bij met nieuwe gegevens
        $promotion->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'cta_text' => $validated['cta_text'],
            'valid_until' => $validated['valid_until']
        ]);

        // Synchroniseer gekoppelde producten met nieuwe kortingsprijzen
        $promotion->products()->sync(collect($validated['products'])->mapWithKeys(function ($item) {
            return [$item['id'] => ['discount_price' => $item['discount_price']]];
        }));

        return redirect()->route('editor.promotions.index');
    }

    /**
     * Verwijder een promotie uit de database
     * 
     * @param \App\Models\Promotion $promotion
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Promotion $promotion)
    {
        // Verwijder bijbehorende afbeelding van storage
        Storage::disk('public')->delete($promotion->image_path);
        
        // Ontkoppel alle producten van de promotie
        $promotion->products()->detach();
        
        // Verwijder de promotie uit de database
        $promotion->delete();
        
        return redirect()->route('editor.promotions.index');
    }
}