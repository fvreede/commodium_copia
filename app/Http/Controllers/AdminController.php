<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Product;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'users' => User::count(),
            'categories' => Category::count(),
            'subcategories' => Subcategory::count(),
            'products' => Product::count(),
        ];

        return Inertia::render('Admin/Dashboard/Index', [
            'stats' => $stats,
        ]);
    }
}
