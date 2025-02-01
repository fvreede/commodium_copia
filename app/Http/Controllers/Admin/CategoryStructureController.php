<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryStructureController extends Controller
{
    public function index()
    {
        $categories = Category::withCount(['subcategories' => function ($query) {
            $query->withoutTrashed();
        }])->withoutTrashed()->get();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' =>'required|string|max:255|unique:categories,name,NULL,id,deleted_at,NULL',
        ]);

        Category::create([
            'name' => $validated['name'],
            'banner_path' => null // This will be managed by editors
        ]);

        return redirect()->route('admin.categories.index');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' =>'required|string|max:255|unique:categories,name,'.$category->id.',id,deleted_at,NULL',
        ]);

        $category->update([
            'name' => $validated['name']
        ]);

        return redirect()->route('admin.categories.index');
    }

    public function destroy(Category $category)
    {
        // Checks if category has active subcategories
        if ($category->subcategories()->withoutTrashed()->exists()) {
            return back()->withErrors([
                'error' => 'Cannot delete category that has active subcategories.'
            ]);
        }

        $category->delete(); // This is a soft delete
        return redirect()->route('admin.categories.index');
    }
}
