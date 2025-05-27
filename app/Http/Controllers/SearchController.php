<?php

// /app/Http/Controllers/SearchController.php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SearchController extends Controller
{
    /**
     * Display search results page
     */
    public function index(Request $request)
    {
        $query = $request->get('q', '');
        $products = collect();

        if (strlen($query) >= 2) {
            $products = Product::with(['subcategory.category'])
                ->where('name', 'LIKE', "%{$query}%") // Only search in name field
                ->orderBy('name')
                ->get()
                ->map(function ($product) {
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
            'resultsCount' => $products->count() // Changed from totalProducts to match your Vue component
        ]);
    }

    /**
     * API endpoint for live search suggestions
     */
    public function suggestions(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $products = Product::with(['subcategory.category'])
            ->where('name', 'LIKE', "%{$query}%")
            ->orderBy('name')
            ->limit(8)
            ->get()
            ->map(function ($product) {
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
     * Get popular/trending products for search suggestions
     */
    public function popular(Request $request)
    {
        $products = Product::with(['subcategory.category'])
            ->orderBy('name') // You might want to change this to order by popularity/sales
            ->limit(6)
            ->get()
            ->map(function ($product) {
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