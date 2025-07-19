<?php

/**
 * Bestandsnaam: EditorController.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.2
 * Datum: 2025-05-22
 * Tijd: 13:57:04
 * Doel: Controller voor de editor interface. Bevat dashboard functionaliteit en XML export endpoints voor producten, categorieën en promoties voor externe systemen en integraties.
 */

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Product;
use SimpleXMLElement;
use Inertia\Inertia;

class EditorController extends Controller
{
    /**
     * Toon het editor dashboard met statistieken
     * 
     * @return \Inertia\Response
     */
    public function dashboard()
    {
        // Verzamel statistieken voor het editor dashboard
        $stats = [
            'categories' => Category::count(),       // Totaal aantal hoofdcategorieën
            'subcategories' => Subcategory::count(), // Totaal aantal subcategorieën
            'products' => Product::count(),          // Totaal aantal producten
        ];

        return Inertia::render('Editor/Dashboard/Index', [
            'stats' => $stats,
        ]);
    }

    /**
     * Exporteer alle producten als XML bestand
     * Gebruikt voor externe systemen en product feeds
     * 
     * @return \Illuminate\Http\Response
     */
    public function productsXml()
    {
        // Haal alle producten op met bijbehorende subcategorie en categorie relaties
        $products = Product::with('subcategory.category')->get();
        
        // Maak een nieuwe XML structuur aan
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Products></Products>');

        // Loop door alle producten en voeg ze toe aan de XML
        foreach ($products as $product) {
            $item = $xml->addChild('Product');
            $item->addAttribute('Id', $product->id);
            
            // Voeg product eigenschappen toe als XML elementen
            $item->addChild('EAN', $product->ean);
            $item->addChild('Title', $product->name);
            $item->addChild('Brand', $product->brand);
            $item->addChild('Shortdescription', $product->description);
            $item->addChild('Fulldescription', $product->fullDescription);
            $item->addChild('Image', $product->image_path);
            $item->addChild('Weight', (string) $product->weight);
            $item->addChild('Price', (string) $product->price);
            
            // Voeg categorie informatie toe
            $item->addChild('Category', $product->subcategory->category->name);
            $item->addChild('Subcategory', $product->subcategory->name);
        }

        // Return XML response met juiste content-type header
        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }

    /**
     * Exporteer alle categorieën en subcategorieën als XML bestand
     * Gebruikt voor externe systemen en categorie synchronisatie
     * 
     * @return \Illuminate\Http\Response
     */
    public function categoriesXml()
    {
        // Haal alle categorieën op met bijbehorende subcategorieën
        $categories = Category::with('subcategories')->get();
        
        // Maak een nieuwe XML structuur aan
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Categories></Categories>');

        // Loop door alle categorieën
        foreach ($categories as $category) {
            $catXml = $xml->addChild('Category');
            $catXml->addChild('Name', $category->name);

            // Voeg alle subcategorieën toe binnen elke hoofdcategorie
            foreach ($category->subcategories as $sub) {
                $subXml = $catXml->addChild('Subcategory');
                $subXml->addChild('Name', $sub->name);
            }
        }

        // Return XML response met juiste content-type header
        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }

    /**
     * Exporteer alle promoties met gekoppelde producten als XML bestand
     * Gebruikt voor externe systemen en promotie synchronisatie
     * 
     * @return \Illuminate\Http\Response
     */
    public function promotionsXml()
    {
        // Haal alle promoties op met bijbehorende producten (many-to-many relatie)
        $promotions = Promotion::with('products')->get();
        
        // Maak een nieuwe XML structuur aan
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Promotions></Promotions>');

        // Loop door alle promoties
        foreach ($promotions as $promo) {
            $promoXml = $xml->addChild('Promotion');
            $promoXml->addChild('Title', $promo->title);

            // Voeg alle gekoppelde producten met hun kortingsprijzen toe
            foreach ($promo->products as $product) {
                $discountXml = $promoXml->addChild('Discount');
                $discountXml->addAttribute('Id', $product->pivot->id);
                
                // Voeg product en korting informatie toe uit de pivot table
                $discountXml->addChild('EAN', $product->ean);
                $discountXml->addChild('DiscountPrice', (string) $product->pivot->discount_price);
                $discountXml->addChild('ValidUntil', (string) $promo->valid_until);
            }
        }

        // Return XML response met juiste content-type header
        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }
}