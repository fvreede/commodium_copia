<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubcategoryController extends Controller
{
    public function index()
    {
        $subcategories = Subcategory::with(['category', 'products'])->get();
        $categories = Category::all();
        
        return Inertia::render('Editor/Subcategories/Index', [
            'subcategories' => $subcategories,
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id'
        ]);

        Subcategory::create($validated);

        return redirect()->route('editor.subcategories.index');
    }

    public function update(Request $request, Subcategory $subcategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id'
        ]);

        $subcategory->update($validated);

        return redirect()->route('editor.subcategories.index');
    }

    public function destroy(Subcategory $subcategory)
    {
        $subcategory->delete();
        return redirect()->route('editor.subcategories.index');
    }
}