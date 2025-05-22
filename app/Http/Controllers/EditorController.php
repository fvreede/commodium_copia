<?php

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
    public function dashboard()
    {
        $stats = [
            'categories' => Category::count(),
            'subcategories' => Subcategory::count(),
            'products' => Product::count(),
        ];

        return Inertia::render('Editor/Dashboard/Index', [
            'stats' => $stats,
        ]);
    }

    public function productsXml()
    {
        $products = Product::with('subcategory.category')->get();
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Products></Products>');

        foreach ($products as $product) {
            $item = $xml->addChild('Product');
            $item->addAttribute('Id', $product->id);
            $item->addChild('EAN', $product->ean);
            $item->addChild('Title', $product->name);
            $item->addChild('Brand', $product->brand);
            $item->addChild('Shortdescription', $product->description);
            $item->addChild('Fulldescription', $product->fullDescription);
            $item->addChild('Image', $product->image_path);
            $item->addChild('Weight', $product->weight);
            $item->addChild('Price', $product->price);
            $item->addChild('Category', $product->subcategory->category->name);
            $item->addChild('Subcategory', $product->subcategory->name);
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }

    public function categoriesXml()
    {
        $categories = Category::with('subcategories')->get();
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Categories></Categories>');

        foreach ($categories as $category) {
            $catXml = $xml->addChild('Category');
            $catXml->addChild('Name', $category->name);
            
            foreach ($category->subcategories as $sub) {
                $subXml = $catXml->addChild('Subcategory');
                $subXml->addChild('Name', $sub->name);
            }
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }

    public function promotionsXml()
    {
        $promotions = Promotion::with('products')->get();
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Promotions></Promotions>');

        foreach ($promotions as $promo) {
            $promoXml = $xml->addChild('Promotion');
            $promoXml->addChild('Title', $promo->title);
            
            foreach ($promo->products as $product) {
                $discountXml = $promoXml->addChild('Discount');
                $discountXml->addAttribute('Id', $product->pivot->id);
                $discountXml->addChild('EAN', $product->ean);
                $discountXml->addChild('DiscountPrice', $product->pivot->discount_price);
                $discountXml->addChild('ValidUntil', $promo->valid_until);
            }
        }

        return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
    }
}
