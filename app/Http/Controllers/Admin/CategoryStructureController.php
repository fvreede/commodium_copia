<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Spatie\Image\Image;

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
            'description' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'imagePosition.x' => 'required|numeric',
            'imagePosition.y' => 'required|numeric'
        ]);
        
        if ($request->hasFile('image')) {
            $filename = time() . '_' . uniqid() . '.jpg';
            $path = 'images/categories/'. $filename;
            $fullPath = Storage::disk('public')->path($path);

            Image::load($request->file('image'))
                ->manualCrop(1000, 1000, abs($validated['imagePosition']['x']), abs($validated['imagePosition']['y']))
                ->optimize()
                ->save($fullPath);

            Category::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'image_path' => $path,
                'banner_path' => null // This will be managed by editors
            ]);

            return redirect()->route('admin.categories.index');
        }

        return back()->withErrors(['image' => 'Error processing image']);
    }

    public function edit(Category $category)
{
    $imagePath = storage_path('app/public/' . $category->image_path);
    
    \Log::info('Category image debug', [
        'database_path' => $category->image_path,
        'full_storage_path' => $imagePath,
        'exists' => file_exists($imagePath),
        'storage_files' => scandir(storage_path('app/public/images/categories'))
    ]);

    return Inertia::render('Admin/Categories/Edit', [
        'category' => array_merge($category->toArray(), [
            '_debug' => [
                'image_exists' => file_exists($imagePath)
            ]
        ])
    ]);
}

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->ignore($category->id)],
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'imagePosition.x' => 'nullable|required_with:image|numeric',
            'imagePosition.y' => 'nullable|required_with:image|numeric'
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }

            $filename = time() . '_' . uniqid() . '.jpg';
            $path = 'images/categories/'. $filename;
            $fullPath = Storage::disk('public')->path($path);

            Image::load($request->file('image'))
                ->manualCrop(1000, 1000, abs($validated['imagePosition']['x']), abs($validated['imagePosition']['y']))
                ->optimize()
                ->save($fullPath);

            $validated['image_path'] = $path;
        }

        $category->update($validated);

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