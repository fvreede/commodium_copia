<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Product;
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
}
