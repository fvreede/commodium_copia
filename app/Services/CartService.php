<?php

/**
 * Bestandsnaam: CartService.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.3
 * Datum: 2025-06-10
 * Tijd: 23:44:54
 * Doel: Service klasse voor winkelwagen beheer. Behandelt cart operaties voor zowel anonieme gebruikers (sessie) als ingelogde gebruikers (database), inclusief voorraad validatie, cart migratie en promotie prijzen.
 */

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CartService
{
    /**
     * Sessie sleutel voor anonieme winkelwagen opslag
     */
    private const SESSION_KEY = 'cart';

    /**
     * PUBLIEKE CART OPERATIES
     */

    /**
     * Haal alle winkelwagen items op voor huidige gebruiker (sessie of database)
     * Bepaalt automatisch de juiste opslag methode op basis van authenticatie status
     * 
     * @return array Lijst van cart items met product informatie
     */
    public function getItems(): array
    {
        if (Auth::check()) {
            return $this->getDatabaseItems();
        }
        return $this->getSessionItems();
    }

    /**
     * Voeg item toe aan winkelwagen met uitgebreide voorraad validatie
     * Controleert product beschikbaarheid, voorraad en huidige cart inhoud
     * 
     * @param int $productId ID van het product om toe te voegen
     * @param int $quantity Hoeveelheid om toe te voegen (standaard 1)
     * @return bool True bij succes, false bij falen
     */
    public function addItem(int $productId, int $quantity = 1): bool
    {
        Log::info('CartService::addItem called', [
            'product_id' => $productId,
            'quantity' => $quantity,
            'auth_check' => Auth::check(),
            'user_id' => Auth::id()
        ]);

        // Valideer hoeveelheid (moet positief zijn)
        if ($quantity <= 0) {
            Log::error('Invalid quantity provided', ['quantity' => $quantity]);
            return false;
        }

        // Zoek product en valideer beschikbaarheid
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

        // Controleer of product actief is
        if (!$product->is_active) {
            Log::warning('Product is not active', ['product_id' => $productId]);
            return false;
        }

        // Controleer voorraad beschikbaarheid
        if ($product->stock_quantity <= 0) {
            Log::warning('Product is out of stock', ['product_id' => $productId]);
            return false;
        }

        // Controleer totale hoeveelheid in cart vs beschikbare voorraad
        $currentQuantity = $this->getProductQuantity($productId);
        $totalQuantity = $currentQuantity + $quantity;

        if ($totalQuantity > $product->stock_quantity) {
            Log::warning('Insufficient stock', [
                'product_id' => $productId,
                'current_quantity' => $currentQuantity,
                'requested_quantity' => $quantity,
                'available_stock' => $product->stock_quantity
            ]);
            return false;
        }

        // Voeg toe aan juiste opslag (database of sessie)
        if (Auth::check()) {
            return $this->addToDatabaseCart($product, $quantity);
        }
        return $this->addToSessionCart($product, $quantity);
    }

    /**
     * Werk hoeveelheid van item in winkelwagen bij
     * Valideert nieuwe hoeveelheid tegen beschikbare voorraad
     * 
     * @param int $productId ID van het product
     * @param int $quantity Nieuwe hoeveelheid (0 = verwijderen)
     * @return bool True bij succes, false bij falen
     */
    public function updateQuantity(int $productId, int $quantity): bool
    {
        if ($quantity < 0) {
            return false;
        }

        // Hoeveelheid 0 betekent item verwijderen
        if ($quantity === 0) {
            return $this->removeItem($productId);
        }

        // Valideer product en voorraad
        $product = Product::find($productId);
        if (!$product || !$product->is_active) {
            return false;
        }

        // Controleer voorraad beschikbaarheid voor nieuwe hoeveelheid
        if ($quantity > $product->stock_quantity) {
            Log::warning('Update quantity exceeds stock', [
                'product_id' => $productId,
                'requested_quantity' => $quantity,
                'available_stock' => $product->stock_quantity
            ]);
            return false;
        }

        // Update in juiste opslag
        if (Auth::check()) {
            return $this->updateDatabaseQuantity($productId, $quantity);
        }
        return $this->updateSessionQuantity($productId, $quantity);
    }

    /**
     * Verwijder item uit winkelwagen
     * 
     * @param int $productId ID van het product om te verwijderen
     * @return bool True bij succes, false bij falen
     */
    public function removeItem(int $productId): bool
    {
        if (Auth::check()) {
            return $this->removeFromDatabaseCart($productId);
        }
        return $this->removeFromSessionCart($productId);
    }

    /**
     * Maak gehele winkelwagen leeg
     * 
     * @return void
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
     * Bereken winkelwagen totalen
     * Retourneert subtotaal, totaal en item aantallen
     * 
     * @return array Array met subtotal, total, total_items, items_count
     */
    public function getTotals(): array
    {
        $items = $this->getItems();
        $subtotal = 0;
        $totalItems = 0;
        $itemsCount = count($items);

        // Bereken totalen door alle items te itereren
        foreach ($items as $item) {
            $subtotal += ($item['price'] * $item['quantity']);
            $totalItems += $item['quantity'];
        }

        return [
            'subtotal' => round($subtotal, 2),
            'total' => round($subtotal, 2), // Voeg BTW/verzendkosten logica toe indien nodig
            'total_items' => $totalItems,
            'items_count' => $itemsCount
        ];
    }

    /**
     * Krijg huidige hoeveelheid van specifiek product in winkelwagen
     * 
     * @param int $productId ID van het product
     * @return int Huidige hoeveelheid in cart (0 als niet aanwezig)
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
     * CART MIGRATIE FUNCTIONALITEIT
     */

    /**
     * Migreer sessie winkelwagen naar database wanneer gebruiker inlogt
     * Combineert bestaande database items met sessie items, respecteert voorraad limieten
     * 
     * @return void
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

        // Gebruik database transactie voor consistente migratie
        DB::transaction(function () use ($sessionItems) {
            foreach ($sessionItems as $item) {
                // Controleer of item al bestaat in database cart
                $existingItem = CartItem::where('user_id', Auth::id())
                    ->where('product_id', $item['product_id'])
                    ->first();

                if ($existingItem) {
                    // Update bestaand item, respecteer voorraad limieten
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $newQuantity = min(
                            $existingItem->quantity + $item['quantity'],
                            $product->stock_quantity
                        );
                        $existingItem->update(['quantity' => $newQuantity]);
                    }
                } else {
                    // Maak nieuw cart item aan
                    CartItem::create([
                        'user_id' => Auth::id(),
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price']
                    ]);
                }
            }
        });

        // Leeg sessie cart na succesvolle migratie
        $this->clearSessionCart();
    }

    /**
     * PRIVATE HELPER METHODES - DATABASE OPERATIES
     */

    /**
     * Haal items op uit database voor geauthenticeerde gebruikers
     * Filtert automatisch inactieve producten en verwijdert ze uit cart
     * 
     * @return array Lijst van cart items met product details
     */
    private function getDatabaseItems(): array
    {
        $cartItems = CartItem::with(['product.subcategory.category'])
            ->where('user_id', Auth::id())
            ->get();

        return $cartItems->map(function ($item) {
            // Verwijder inactieve producten automatisch uit cart
            if (!$item->product || !$item->product->is_active) {
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
     * Haal items op uit sessie voor gast gebruikers
     * Valideert product beschikbaarheid en verwijdert inactieve items
     * 
     * @return array Lijst van cart items uit sessie
     */
    private function getSessionItems(): array
    {
        $cart = Session::get(self::SESSION_KEY, []);
        $items = [];

        foreach ($cart as $productId => $cartItem) {
            $product = Product::find($productId);
            
            // Verwijder inactieve producten uit sessie cart
            if (!$product || !$product->is_active) {
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

        // Update sessie als we inactieve producten hebben verwijderd
        Session::put(self::SESSION_KEY, $cart);
        return $items;
    }

    /**
     * Voeg item toe aan database cart
     * Gebruikt database transactie voor consistentie
     * 
     * @param Product $product Product om toe te voegen
     * @param int $quantity Hoeveelheid om toe te voegen
     * @return bool True bij succes, false bij falen
     */
    private function addToDatabaseCart(Product $product, int $quantity): bool
    {
        try {
            DB::transaction(function () use ($product, $quantity) {
                $existingItem = CartItem::where('user_id', Auth::id())
                    ->where('product_id', $product->id)
                    ->first();

                if ($existingItem) {
                    // Update bestaand item, respecteer voorraad limiet
                    $newQuantity = min(
                        $existingItem->quantity + $quantity,
                        $product->stock_quantity
                    );
                    $existingItem->update(['quantity' => $newQuantity]);
                } else {
                    // Maak nieuw cart item aan
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
     * Voeg item toe aan sessie cart
     * Met uitgebreide logging voor debugging
     * 
     * @param Product $product Product om toe te voegen
     * @param int $quantity Hoeveelheid om toe te voegen
     * @return bool True bij succes, false bij falen
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
                // Update bestaand item, respecteer voorraad limiet
                $newQuantity = min(
                    $cart[$productId]['quantity'] + $quantity,
                    $product->stock_quantity
                );
                $cart[$productId]['quantity'] = $newQuantity;
            } else {
                // Voeg nieuw item toe
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
     * Krijg product prijs veilig met promotie ondersteuning
     * Valt terug op normale prijs als promotie berekening faalt
     * 
     * @param Product $product Product om prijs voor op te halen
     * @return float Huidige prijs (met promotie indien van toepassing)
     */
    private function getProductPrice(Product $product): float
    {
        try {
            // Probeer huidige prijs op te halen (inclusief promoties)
            return $product->getCurrentPrice();
        } catch (\Exception $e) {
            Log::warning('Error getting current price, falling back to regular price', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
            // Valt terug op normale prijs als er problemen zijn met promoties
            return (float) $product->price;
        }
    }

    /**
     * QUANTITY UPDATE OPERATIES
     */

    /**
     * Werk hoeveelheid bij in database cart
     * 
     * @param int $productId Product ID
     * @param int $quantity Nieuwe hoeveelheid
     * @return bool True bij succes, false bij falen
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
     * Werk hoeveelheid bij in sessie cart
     * 
     * @param int $productId Product ID
     * @param int $quantity Nieuwe hoeveelheid
     * @return bool True bij succes, false bij falen
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
     * ITEM VERWIJDERING OPERATIES
     */

    /**
     * Verwijder item uit database cart
     * 
     * @param int $productId Product ID om te verwijderen
     * @return bool True bij succes, false bij falen
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
     * Verwijder item uit sessie cart
     * 
     * @param int $productId Product ID om te verwijderen
     * @return bool Altijd true voor sessie operaties
     */
    private function removeFromSessionCart(int $productId): bool
    {
        $cart = Session::get(self::SESSION_KEY, []);
        unset($cart[$productId]);
        Session::put(self::SESSION_KEY, $cart);
        return true;
    }

    /**
     * CART CLEAR OPERATIES
     */

    /**
     * Maak database cart leeg voor huidige gebruiker
     * 
     * @return void
     */
    private function clearDatabaseCart(): void
    {
        CartItem::where('user_id', Auth::id())->delete();
    }

    /**
     * Maak sessie cart leeg
     * 
     * @return void
     */
    private function clearSessionCart(): void
    {
        Session::forget(self::SESSION_KEY);
    }
}