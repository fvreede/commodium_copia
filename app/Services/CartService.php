<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

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
        // Debug logging
        Log::info('CartService::addItem called', [
            'product_id' => $productId,
            'quantity' => $quantity,
            'auth_check' => Auth::check(),
            'user_id' => Auth::id()
        ]);

        // Try to find the product
        $product = Product::find($productId);
        
        if (!$product) {
            Log::error('Product not found', ['product_id' => $productId]);
            return false;
        }

        Log::info('Product found', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'is_active' => $product->is_active ?? 'N/A',
            'stock_quantity' => $product->stock_quantity ?? 'N/A'
        ]);

        // Check if product is active (if this column exists)
        if (isset($product->is_active) && !$product->is_active) {
            Log::error('Product is not active', ['product_id' => $productId]);
            return false;
        }

        // Check stock - handle case where stock_quantity might not exist
        $stockQuantity = $product->stock_quantity ?? 999; // Default high stock if column doesn't exist
        if ($stockQuantity < $quantity) {
            Log::error('Insufficient stock', [
                'product_id' => $productId,
                'requested_quantity' => $quantity,
                'available_stock' => $stockQuantity
            ]);
            return false;
        }

        if (Auth::check()) {
            $result = $this->addDatabaseItem($product, $quantity);
            Log::info('Database add result', ['success' => $result]);
            return $result;
        }
        
        $result = $this->addSessionItem($product, $quantity);
        Log::info('Session add result', ['success' => $result]);
        return $result;
    }

    /**
     * Update item quantity
     */
    public function updateQuantity(int $productId, int $quantity): bool
    {
        if ($quantity <= 0) {
            return $this->removeItem($productId);
        }

        $product = Product::find($productId);
        if (!$product) {
            return false;
        }

        $stockQuantity = $product->stock_quantity ?? 999;
        if ($stockQuantity < $quantity) {
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
                $product = Product::find($item['product_id']);
                
                // Check stock before updating
                if ($product && $newQuantity <= ($product->stock_quantity ?? 999)) {
                    $existingItem->update(['quantity' => $newQuantity]);
                }
            } else {
                // Create new cart item
                $product = Product::find($item['product_id']);
                if ($product && $item['quantity'] <= ($product->stock_quantity ?? 999)) {
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
                'stock_quantity' => $item->product->stock_quantity ?? 999,
                'total' => $item->quantity * $item->price,
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

        // Remove the is_active filter temporarily for debugging
        $products = Product::whereIn('id', $productIds)
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
                    'stock_quantity' => $product->stock_quantity ?? 999,
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
        try {
            $existingItem = CartItem::where('user_id', Auth::id())
                ->where('product_id', $product->id)
                ->first();

            if ($existingItem) {
                $newQuantity = $existingItem->quantity + $quantity;
                $stockQuantity = $product->stock_quantity ?? 999;
                if ($newQuantity > $stockQuantity) {
                    Log::error('Database item update: insufficient stock', [
                        'existing_quantity' => $existingItem->quantity,
                        'add_quantity' => $quantity,
                        'new_quantity' => $newQuantity,
                        'available_stock' => $stockQuantity
                    ]);
                    return false;
                }
                $existingItem->update(['quantity' => $newQuantity]);
                Log::info('Updated existing cart item', ['new_quantity' => $newQuantity]);
            } else {
                CartItem::create([
                    'user_id' => Auth::id(),
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->price,
                ]);
                Log::info('Created new cart item');
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error in addDatabaseItem', [
                'error' => $e->getMessage(),
                'product_id' => $product->id,
                'quantity' => $quantity
            ]);
            return false;
        }
    }

    /**
     * Add item to session
     */
    private function addSessionItem(Product $product, int $quantity): bool
    {
        try {
            $cart = Session::get(self::SESSION_KEY, []);
            
            if (isset($cart[$product->id])) {
                $newQuantity = $cart[$product->id]['quantity'] + $quantity;
                $stockQuantity = $product->stock_quantity ?? 999;
                if ($newQuantity > $stockQuantity) {
                    Log::error('Session item update: insufficient stock', [
                        'existing_quantity' => $cart[$product->id]['quantity'],
                        'add_quantity' => $quantity,
                        'new_quantity' => $newQuantity,
                        'available_stock' => $stockQuantity
                    ]);
                    return false;
                }
                $cart[$product->id]['quantity'] = $newQuantity;
                Log::info('Updated existing session item', ['new_quantity' => $newQuantity]);
            } else {
                $cart[$product->id] = [
                    'quantity' => $quantity,
                    'price' => $product->price,
                ];
                Log::info('Created new session item');
            }

            Session::put(self::SESSION_KEY, $cart);
            return true;
        } catch (\Exception $e) {
            Log::error('Error in addSessionItem', [
                'error' => $e->getMessage(),
                'product_id' => $product->id,
                'quantity' => $quantity
            ]);
            return false;
        }
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