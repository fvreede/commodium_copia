<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PromotionController extends Controller
{
    public function index()
    {
        return Inertia::render('Editor/Promotions/Index', [
            'promotions' => Promotion::with('products')->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Editor/Promotions/Create', [
            'products' => Product::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'cta_text' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'valid_until' => 'required|date',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.discount_price' => 'required|numeric|min:0'
       ]);

       $path = $request->file('image')->store('images/promotions', 'public');

       $promotion = Promotion::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'cta_text' => $validated['cta_text'],
            'image_path' => $path,
            'valid_until' => $validated['valid_until'],
            'is_active' => true
       ]);

       foreach ($validated['products'] as $product) {
            $promotion->products()->attach($product['id'], [
                'discount_price' => $product['discount_price']
            ]);
        }

        return redirect()->route('editor.promotions.index');
    }

    public function edit(Promotion $promotion)
    {
        return Inertia::render('Editor/Promotions/Edit', [
            'promotion' => $promotion->load('products'),
            'products' => Product::all()
        ]);
    }

    public function update(Request $request, Promotion $promotion)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'cta_text' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'valid_until' => 'required|date',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.discount_price' => 'required|numeric|min:0'
        ]);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($promotion->image_path);
            $path = $request->file('image')->store('images/promotions', 'public');
            $promotion->image_path = $path;
        }

        $promotion->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'cta_text' => $validated['cta_text'],
            'valid_until' => $validated['valid_until']
        ]);


        $promotion->products()->sync(collect($validated['products'])->mapWithKeys(function ($item) {
            return [$item['id'] => ['discount_price' => $item['discount_price']]];
        }));

        return redirect()->route('editor.promotions.index');
    }

    public function destroy(Promotion $promotion)
    {
        Storage::disk('public')->delete($promotion->image_path);
        $promotion->products()->detach();
        $promotion->delete();
        return redirect()->route('editor.promotions.index');
    }
}
