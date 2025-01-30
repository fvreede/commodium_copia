<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class BannerController extends Controller
{
    public function index()
    {
        return Inertia::render('Editor/Banners/Index', [
            'categories' => Category::all()
        ]);
    }

    public function edit(Category $category)
    {
        return Inertia::render('Editor/Banners/Edit', [
            'category' => $category,
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'banner' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Delete old banner if exists
        if ($category->banner_path) {
            Storage::disk('public')->delete($category->banner_path);
        }

        // Store new banner
        $path = $request->file('banner')->store('images/subcategories/banners', 'public');

        // Update category
        $category->update([
            'banner_path' => $path,
        ]);

        return redirect()->route('editor.banners.index');
    }
}
