<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CategoryStructureController extends Controller
{
    public function index()
    {
        $categories = Category::withCount(['subcategories' => function($query) {
            $query->withoutTrashed();
        }])->withoutTrashed()->get();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Categories/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')],
        ]);

        Category::create([
            'name' => $validated['name'],
            'banner_path' => null // This will be managed by editors
        ]);

        return redirect()->route('admin.categories.index');
    }

    public function edit(Category $category)
    {
        return Inertia::render('Admin/Categories/Edit', [
            'category' => $category
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->ignore($category->id)],
        ]);

        $category->update([
            'name' => $validated['name']
        ]);

        return redirect()->route('admin.categories.index');
    }

    public function destroy(Category $category)
    {
        if ($category->subcategories()->withoutTrashed()->exists()) {
            return back()->withErrors([
                'error' => 'Cannot delete category that has active subcategories.'
            ]);
        }

        $category->delete();
        return redirect()->route('admin.categories.index');
    }
}