<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('subcategories')->get();
        return Inertia::render('Editor/Categories/Index', [
            'categories' => $categories
        ]);
    }

    public function create()
    {
        return Inertia::render('Editor/Categories/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'banner' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $path = $request->file('banner')->store('images/subcategories/banners', 'public');
        
        Category::create([
            'name' => $validated['name'],
            'banner_path' => $path
        ]);

        return redirect()->route('editor.categories.index');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('banner')) {
            Storage::disk('public')->delete($category->banner_path);
            $path = $request->file('banner')->store('images/subcategories/banners', 'public');
            $category->banner_path = $path;
        }

        $category->name = $validated['name'];
        $category->save();

        return redirect()->route('editor.categories.index');
    }

    public function destroy(Category $category)
    {
        if ($category->banner_path) {
            Storage::disk('public')->delete($category->banner_path);
        }
        $category->delete();

        return redirect()->route('editor.categories.index');
    }
}