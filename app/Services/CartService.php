<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    private const SESSION_KEY = 'cart';

    /**
     * Get all cart items for current user (session or database)
     */
    public function getItems(): array
    {
        if (Auth::check()) {
            return $this->getDatabaseItems();
        }
        
        return $this->getSessionItems();
    }

    /**
     * Add item to cart
     */
    public function addItem(int $productId, int $quantity = 1): bool
    {
        $product = Product::findOrFail($productId);
        
        if (!$product || !$product->is_active) {
            return false;
        }

        // Check stock
        if ($product->stock_quantity < $quantity) {
            return false;
        }

        if (Auth::check()) {
            return $this->addDatabaseItem($product, $quantity);
        }
        
        return $this->addSessionItem($product, $quantity);
    }

    /**
     * Update item quantity
     */
    public function updateQuantity(int $productId, int $quantity): bool
    {
        if ($quantity <= 0) {
            return $this->removeItem($productId);
        }

        $product = Product::findOrFail($productId);
        if (!$product || $product->stock_quantity < $quantity) {
            return false;
        }

        if (Auth::check()) {
            return $this->updateDatabaseQuantity($productId, $quantity);
        }
        
        return $this->updateSessionQuantity($productId, $quantity);
    }

    /**
     * Remove item from cart
     */
    public function removeItem(int $productId): bool
    {
        if (Auth::check()) {
            return $this->removeDatabaseItem($productId);
        }
        
        return $this->removeSessionItem($productId);
    }

    /**
     * Clear entire cart
     */
    public function clear(): bool
    {
        if (Auth::check()) {
            CartItem::where('user_id', Auth::id())->delete();
            return true;
        }
        
        Session::forget(self::SESSION_KEY);
        return true;
    }

    /**
     * Get cart totals
     */
    public function getTotals(): array
    {
        $items = $this->getItems();
        
        $subtotal = collect($items)->sum(function ($item) {
            return $item['quantity'] * $item['price'];
        });
        
        $totalItems = collect($items)->sum('quantity');
        
        return [
            'subtotal' => $subtotal,
            'total' => $subtotal, // Add tax/shipping logic here if needed
            'total_items' => $totalItems,
            'items_count' => count($items),
        ];
    }

    /**
     * Migrate session cart to database when user logs in
     */
    public function migrateSessionToDatabase(int $userId): void
    {
        $sessionItems = $this->getSessionItems();
        
        if (empty($sessionItems)) {
            return;
        }

        foreach ($sessionItems as $item) {
            $existingItem = CartItem::where('user_id', $userId)
                ->where('product_id', $item['product_id'])
                ->first();

            if ($existingItem) {
                // If item exists in database, add session quantity to it
                $newQuantity = $existingItem->quantity + $item['quantity'];
                $product = Product::findOrFail($item['product_id']);
                
                // Check stock before updating
                if ($product && $newQuantity <= $product->stock_quantity) {
                    $existingItem->update(['quantity' => $newQuantity]);
                }
            } else {
                // Create new cart item
                $product = Product::find($item['product_id']);
                if ($product && $item['quantity'] <= $product->stock_quantity) {
                    CartItem::create([
                        'user_id' => $userId,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                }
            }
        }

        // Clear session cart after migration
        Session::forget(self::SESSION_KEY);
    }

    /**
     * Get items from database for authenticated user
     */
    private function getDatabaseItems(): array
    {
        $cartItems = CartItem::with('product')
            ->where('user_id', Auth::id())
            ->get();

        return $cartItems->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'image_path' => $item->product->image_path,
                'stock_quantity' => $item->product->stock_quantity,
                'total' => $item->total,
            ];
        })->toArray();
    }

    /**
     * Get items from session for guest user
     */
    private function getSessionItems(): array
    {
        $sessionCart = Session::get(self::SESSION_KEY, []);
        $productIds = array_keys($sessionCart);
        
        if (empty($productIds)) {
            return [];
        }

        $products = Product::whereIn('id', $productIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        $items = [];
        foreach ($sessionCart as $productId => $sessionItem) {
            if (isset($products[$productId])) {
                $product = $products[$productId];
                $items[] = [
                    'id' => $productId,
                    'product_id' => $productId,
                    'name' => $product->name,
                    'price' => $sessionItem['price'],
                    'quantity' => $sessionItem['quantity'],
                    'image_path' => $product->image_path,
                    'stock_quantity' => $product->stock_quantity,
                    'total' => $sessionItem['quantity'] * $sessionItem['price'],
                ];
            }
        }

        return $items;
    }

    /**
     * Add item to database
     */
    private function addDatabaseItem(Product $product, int $quantity): bool
    {
        $existingItem = CartItem::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->first();

        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $quantity;
            if ($newQuantity > $product->stock_quantity) {
                return false;
            }
            $existingItem->update(['quantity' => $newQuantity]);
        } else {
            CartItem::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->price,
            ]);
        }

        return true;
    }

    /**
     * Add item to session
     */
    private function addSessionItem(Product $product, int $quantity): bool
    {
        $cart = Session::get(self::SESSION_KEY, []);
        
        if (isset($cart[$product->id])) {
            $newQuantity = $cart[$product->id]['quantity'] + $quantity;
            if ($newQuantity > $product->stock_quantity) {
                return false;
            }
            $cart[$product->id]['quantity'] = $newQuantity;
        } else {
            $cart[$product->id] = [
                'quantity' => $quantity,
                'price' => $product->price,
            ];
        }

        Session::put(self::SESSION_KEY, $cart);
        return true;
    }

    /**
     * Update database item quantity
     */
    private function updateDatabaseQuantity(int $productId, int $quantity): bool
    {
        $cartItem = CartItem::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->first();

        if (!$cartItem) {
            return false;
        }

        $cartItem->update(['quantity' => $quantity]);
        return true;
    }

    /**
     * Update session item quantity
     */
    private function updateSessionQuantity(int $productId, int $quantity): bool
    {
        $cart = Session::get(self::SESSION_KEY, []);
        
        if (!isset($cart[$productId])) {
            return false;
        }

        $cart[$productId]['quantity'] = $quantity;
        Session::put(self::SESSION_KEY, $cart);
        return true;
    }

    /**
     * Remove item from database
     */
    private function removeDatabaseItem(int $productId): bool
    {
        CartItem::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->delete();
        return true;
    }

    /**
     * Remove item from session
     */
    private function removeSessionItem(int $productId): bool
    {
        $cart = Session::get(self::SESSION_KEY, []);
        unset($cart[$productId]);
        Session::put(self::SESSION_KEY, $cart);
        return true;
    }
}