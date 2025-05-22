<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    /**
     *  Haalt de winkelwagen items op uit de session.
     */
    public function index()
    {
        $cartItems = session()->get('cart', []);
        return response()->json(['cartItems' => $cartItems]);
    }

    /**
     *  Voeg een product toe aan de winkelwagen.
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = session()->get('cart', []);
        $productId = $request->product_id;

        // Als het product al in de winkelwagen zit, update het aantal
        if (isset($cart[$productId])) {
            $cart($productId)['quantity'] += $request->quantity;
        } else {
            // Anders, voeg het product toe met het aantal en prijs
            $cart[$productId] = [
                'id' => $productId,
                'quantity' => $request->quantity,
                'price' => $request->price,
                'name' => $request->name,
                'image_path' => $request->image_path
            ];
        }

        session()->put('cart', $cart);
        return response()->json(['message' => 'Product toegevoegd aan winkelwagen']);
    }

    /**
     *  Verwijder een product uit de winkelwagen.
     */
    public function remove($productId)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
        }

        return response()->json(['message' => 'Product verwijderd uit winkelwagen']);
    }

    /**
     *  Update de hoeveelheid van een product in de winkelwagen.
     */
    public function update(Request $request, $productId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $request->quantity;
            session()->put('cart', $cart);
        }

        return response()->json(['message' => 'Winkelwagen bijgewerkt']);
    }

    /**
     *  Maakt de winkelwagen leeg.
     */
    public function clear()
    {
        session()->forget('cart');
        return response()->json(['message' => 'Winkelwagen geleegd']);
    }
}
