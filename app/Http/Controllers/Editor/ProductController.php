<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('subcategory')->get();
        $subcategories = Subcategory::all();
        
        return Inertia::render('Editor/Products/Index', [
            'products' => $products,
            'subcategories' => $subcategories
        ]);
    }

    public function create()
    {
        $subcategories = Subcategory::all();
        return Inertia::render('Editor/Products/Create', [
            'subcategories' => $subcategories
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'required|string',
            'full_description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'subcategory_id' => 'required|exists:subcategories,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $subcategory = Subcategory::find($validated['subcategory_id']);
        $categoryName = strtolower(str_replace(' ', '_', $subcategory->category->name));
        $subcategoryName = strtolower(str_replace(' ', '_', $subcategory->name));
        
        $path = $request->file('image')->store("images/products/{$categoryName}/{$subcategoryName}", 'public');

        Product::create([
            'name' => $validated['name'],
            'short_description' => $validated['description'],
            'full_description' => $validated['fullDescription'],
            'price' => $validated['price'],
            'subcategory_id' => $validated['subcategory_id'],
            'image_path' => $path
        ]);

        return redirect()->route('editor.products.index');
    }

    public function edit(Product $product)
    {
        $subcategories = Subcategory::all();
        return Inertia::render('Editor/Products/Edit', [
            'product' => $product,
            'subcategories' => $subcategories
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'required|string',
            'full_description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'subcategory_id' => 'required|exists:subcategories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($product->image_path);
            
            $subcategory = Subcategory::find($validated['subcategory_id']);
            $categoryName = strtolower(str_replace(' ', '_', $subcategory->category->name));
            $subcategoryName = strtolower(str_replace(' ', '_', $subcategory->name));
            
            $path = $request->file('image')->store("images/products/{$categoryName}/{$subcategoryName}", 'public');
            $product->image_path = $path;
        }

        $product->update($validated);
        return redirect()->route('editor.products.index');
    }

    public function destroy(Product $product)
    {
        Storage::disk('public')->delete($product->image_path);
        $product->delete();
        return redirect()->route('editor.products.index');
    }
}