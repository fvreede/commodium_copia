<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
     * Add item to cart with proper stock validation
     */
    public function addItem(int $productId, int $quantity = 1): bool
    {
        Log::info('CartService::addItem called', [
            'product_id' => $productId,
            'quantity' => $quantity,
            'auth_check' => Auth::check(),
            'user_id' => Auth::id()
        ]);

        // Validate quantity
        if ($quantity <= 0) {
            Log::error('Invalid quantity provided', ['quantity' => $quantity]);
            return false;
        }

        // Find the product with stock validation
        $product = Product::find($productId);
        
        if (!$product) {
            Log::error('Product not found', ['product_id' => $productId]);
            return false;
        }

        Log::info('Product found', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'is_active' => $product->is_active,
            'stock_quantity' => $product->stock_quantity
        ]);

        // Check if product is active and has stock
        if (!$product->is_active) {
            Log::warning('Product is not active', ['product_id' => $productId]);
            return false;
        }

        if ($product->stock_quantity <= 0) {
            Log::warning('Product is out of stock', ['product_id' => $productId]);
            return false;
        }

        // Get current quantity in cart
        $currentQuantity = $this->getProductQuantity($productId);
        $totalQuantity = $currentQuantity + $quantity;

        // Check if total quantity would exceed stock
        if ($totalQuantity > $product->stock_quantity) {
            Log::warning('Insufficient stock', [
                'product_id' => $productId,
                'current_quantity' => $currentQuantity,
                'requested_quantity' => $quantity,
                'available_stock' => $product->stock_quantity
            ]);
            return false;
        }

        // Add to appropriate storage
        if (Auth::check()) {
            return $this->addToDatabaseCart($product, $quantity);
        }
        
        return $this->addToSessionCart($product, $quantity);
    }

    /**
     * Update item quantity in cart
     */
    public function updateQuantity(int $productId, int $quantity): bool
    {
        if ($quantity < 0) {
            return false;
        }

        if ($quantity === 0) {
            return $this->removeItem($productId);
        }

        $product = Product::find($productId);
        if (!$product || !$product->is_active) {
            return false;
        }

        // Check stock availability
        if ($quantity > $product->stock_quantity) {
            Log::warning('Update quantity exceeds stock', [
                'product_id' => $productId,
                'requested_quantity' => $quantity,
                'available_stock' => $product->stock_quantity
            ]);
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
            return $this->removeFromDatabaseCart($productId);
        }
        
        return $this->removeFromSessionCart($productId);
    }

    /**
     * Clear entire cart
     */
    public function clear(): void
    {
        if (Auth::check()) {
            $this->clearDatabaseCart();
        } else {
            $this->clearSessionCart();
        }
    }

    /**
     * Get cart totals
     */
    public function getTotals(): array
    {
        $items = $this->getItems();
        
        $subtotal = 0;
        $totalItems = 0;
        $itemsCount = count($items);

        foreach ($items as $item) {
            $subtotal += ($item['price'] * $item['quantity']);
            $totalItems += $item['quantity'];
        }

        return [
            'subtotal' => round($subtotal, 2),
            'total' => round($subtotal, 2), // Add tax/shipping logic here if needed
            'total_items' => $totalItems,
            'items_count' => $itemsCount
        ];
    }

    /**
     * Get current quantity of a specific product in cart
     */
    public function getProductQuantity(int $productId): int
    {
        $items = $this->getItems();
        
        foreach ($items as $item) {
            if ($item['product_id'] == $productId) {
                return $item['quantity'];
            }
        }
        
        return 0;
    }

    /**
     * Transfer session cart to database when user logs in
     */
    public function migrateSessionToDatabase(): void
    {
        if (!Auth::check()) {
            return;
        }

        $sessionItems = $this->getSessionItems();
        if (empty($sessionItems)) {
            return;
        }

        DB::transaction(function () use ($sessionItems) {
            foreach ($sessionItems as $item) {
                $existingItem = CartItem::where('user_id', Auth::id())
                    ->where('product_id', $item['product_id'])
                    ->first();

                if ($existingItem) {
                    // Update quantity, respecting stock limits
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $newQuantity = min(
                            $existingItem->quantity + $item['quantity'],
                            $product->stock_quantity
                        );
                        $existingItem->update(['quantity' => $newQuantity]);
                    }
                } else {
                    // Create new cart item
                    CartItem::create([
                        'user_id' => Auth::id(),
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price']
                    ]);
                }
            }
        });

        // Clear session cart after transfer
        $this->clearSessionCart();
    }

    /**
     * Get items from database for authenticated users
     */
    private function getDatabaseItems(): array
    {
        $cartItems = CartItem::with(['product.subcategory.category'])
            ->where('user_id', Auth::id())
            ->get();

        return $cartItems->map(function ($item) {
            if (!$item->product || !$item->product->is_active) {
                // Remove inactive products from cart
                $item->delete();
                return null;
            }

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'price' => (float) $item->price,
                'quantity' => $item->quantity,
                'stock_quantity' => $item->product->stock_quantity,
                'image_path' => $item->product->image_path,
                'short_description' => $item->product->short_description,
                'is_active' => $item->product->is_active
            ];
        })->filter()->values()->toArray();
    }

    /**
     * Get items from session for guest users
     */
    private function getSessionItems(): array
    {
        $cart = Session::get(self::SESSION_KEY, []);
        $items = [];

        foreach ($cart as $productId => $cartItem) {
            $product = Product::find($productId);
            
            if (!$product || !$product->is_active) {
                // Remove inactive products from session cart
                unset($cart[$productId]);
                continue;
            }

            $items[] = [
                'id' => $productId,
                'product_id' => $productId,
                'name' => $product->name,
                'price' => (float) $cartItem['price'],
                'quantity' => $cartItem['quantity'],
                'stock_quantity' => $product->stock_quantity,
                'image_path' => $product->image_path,
                'short_description' => $product->short_description,
                'is_active' => $product->is_active
            ];
        }

        // Update session if we removed inactive products
        Session::put(self::SESSION_KEY, $cart);

        return $items;
    }

    /**
     * Add item to database cart
     */
    private function addToDatabaseCart(Product $product, int $quantity): bool
    {
        try {
            DB::transaction(function () use ($product, $quantity) {
                $existingItem = CartItem::where('user_id', Auth::id())
                    ->where('product_id', $product->id)
                    ->first();

                if ($existingItem) {
                    $newQuantity = min(
                        $existingItem->quantity + $quantity,
                        $product->stock_quantity
                    );
                    $existingItem->update(['quantity' => $newQuantity]);
                } else {
                    CartItem::create([
                        'user_id' => Auth::id(),
                        'product_id' => $product->id,
                        'quantity' => min($quantity, $product->stock_quantity),
                        'price' => $this->getProductPrice($product)
                    ]);
                }
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Error adding to database cart', [
                'error' => $e->getMessage(),
                'product_id' => $product->id
            ]);
            return false;
        }
    }

    /**
     * Add item to session cart
     */
    private function addToSessionCart(Product $product, int $quantity): bool
    {
        try {
            $cart = Session::get(self::SESSION_KEY, []);
            $productId = $product->id;

            Log::info('Adding to session cart', [
                'product_id' => $productId,
                'quantity' => $quantity,
                'current_cart' => $cart
            ]);

            if (isset($cart[$productId])) {
                $newQuantity = min(
                    $cart[$productId]['quantity'] + $quantity,
                    $product->stock_quantity
                );
                $cart[$productId]['quantity'] = $newQuantity;
            } else {
                $cart[$productId] = [
                    'quantity' => min($quantity, $product->stock_quantity),
                    'price' => $this->getProductPrice($product)
                ];
            }

            Session::put(self::SESSION_KEY, $cart);
            
            Log::info('Session cart updated', [
                'cart' => Session::get(self::SESSION_KEY)
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Error adding to session cart', [
                'error' => $e->getMessage(),
                'product_id' => $product->id,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Get product price safely
     */
    private function getProductPrice(Product $product): float
    {
        try {
            // Try to get current price (with promotions)
            return $product->getCurrentPrice();
        } catch (\Exception $e) {
            Log::warning('Error getting current price, falling back to regular price', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
            // Fall back to regular price if there's an issue with promotions
            return (float) $product->price;
        }
    }

    /**
     * Update quantity in database cart
     */
    private function updateDatabaseQuantity(int $productId, int $quantity): bool
    {
        try {
            $cartItem = CartItem::where('user_id', Auth::id())
                ->where('product_id', $productId)
                ->first();

            if (!$cartItem) {
                return false;
            }

            $cartItem->update(['quantity' => $quantity]);
            return true;
        } catch (\Exception $e) {
            Log::error('Error updating database cart quantity', [
                'error' => $e->getMessage(),
                'product_id' => $productId
            ]);
            return false;
        }
    }

    /**
     * Update quantity in session cart
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
     * Remove item from database cart
     */
    private function removeFromDatabaseCart(int $productId): bool
    {
        try {
            CartItem::where('user_id', Auth::id())
                ->where('product_id', $productId)
                ->delete();
            return true;
        } catch (\Exception $e) {
            Log::error('Error removing from database cart', [
                'error' => $e->getMessage(),
                'product_id' => $productId
            ]);
            return false;
        }
    }

    /**
     * Remove item from session cart
     */
    private function removeFromSessionCart(int $productId): bool
    {
        $cart = Session::get(self::SESSION_KEY, []);
        unset($cart[$productId]);
        Session::put(self::SESSION_KEY, $cart);
        return true;
    }

    /**
     * Clear database cart
     */
    private function clearDatabaseCart(): void
    {
        CartItem::where('user_id', Auth::id())->delete();
    }

    /**
     * Clear session cart
     */
    private function clearSessionCart(): void
    {
        Session::forget(self::SESSION_KEY);
    }
}