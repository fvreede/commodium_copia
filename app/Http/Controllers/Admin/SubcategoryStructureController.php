<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubcategoryStructureController extends Controller
{
    public function index()
    {
        $subcategories = Subcategory::with(['category' => function ($query) {
            $query->withoutTrashed();
        }])->withCount(['products' => function ($query) {
            $query->withoutTrashed();
        }])->withoutTrashed()->get();

        $categories = Category::withoutTrashed()->get();

        return Inertia::render('Admin/Subcategories/Index', [
            'subcategories' => $subcategories,
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => [
                'required',
                'exists::categories,id,deleted_at,NULL'
            ],
        ]);

        Subcategory::create($validated);

        return redirect()->route('admin.subcategories.index');
    }

    public function update(Request $request, Subcategory $subcategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => [
                'required',
                'exists::categories,id,deleted_at,NULL'
            ],
        ]);

        $subcategory->update($validated);

        return redirect()->route('admin.subcategories.index');
    }

    public function destroy(Subcategory $subcategory)
    {
        // Check if subcategory has active products
        if ($subcategory->products()->withoutTrashed()->exists()) {
            return back()->withErrors([
                'error' => 'Cannot delete subcategory that has active products'
            ]);
        }

        $subcategory->delete(); // This is a soft delete
        return redirect()->route('admin.subcategories.index');
    }
}
