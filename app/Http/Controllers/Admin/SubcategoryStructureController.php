<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SubcategoryStructureController extends Controller
{
    public function index()
    {
        $subcategories = Subcategory::with(['category' => function($query) {
            $query->withoutTrashed();
        }])
        ->withCount(['products' => function($query) {
            $query->withoutTrashed();
        }])
        ->withoutTrashed()
        ->get();
        
        $categories = Category::withoutTrashed()->get();
        
        return Inertia::render('Admin/Subcategories/Index', [
            'subcategories' => $subcategories,
            'categories' => $categories
        ]);
    }

    public function create()
    {
        $categories = Category::withoutTrashed()->get();
        
        return Inertia::render('Admin/Subcategories/Create', [
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255',
                Rule::unique('subcategories')
                    ->where('category_id', $request->category_id)
            ],
            'category_id' => ['required', 'exists:categories,id,deleted_at,NULL']
        ]);

        Subcategory::create($validated);

        return redirect()->route('admin.subcategories.index');
    }

    public function edit(Subcategory $subcategory)
    {
        $categories = Category::withoutTrashed()->get();
        
        return Inertia::render('Admin/Subcategories/Edit', [
            'subcategory' => $subcategory,
            'categories' => $categories
        ]);
    }

    public function update(Request $request, Subcategory $subcategory)
    {
        $validated = $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255',
                Rule::unique('subcategories')
                    ->where('category_id', $request->category_id)
                    ->ignore($subcategory->id)
            ],
            'category_id' => ['required', 'exists:categories,id,deleted_at,NULL']
        ]);

        $subcategory->update($validated);

        return redirect()->route('admin.subcategories.index');
    }

    public function destroy(Subcategory $subcategory)
    {
        if ($subcategory->products()->withoutTrashed()->exists()) {
            return back()->withErrors([
                'error' => 'Cannot delete subcategory that has active products.'
            ]);
        }

        $subcategory->delete();
        return redirect()->route('admin.subcategories.index');
    }
}