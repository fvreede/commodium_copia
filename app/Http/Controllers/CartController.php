<?php

/**
 * Bestandsnaam: CartController.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.3
 * Datum: 2025-06-07
 * Tijd: 00:00:09
 * Doel: API Controller voor winkelwagenbeheer. Behandelt alle cart operaties zoals toevoegen, bijwerken, verwijderen van items en het berekenen van totalen via de CartService.
 */

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    protected CartService $cartService;

    /**
     * Constructor - Injecteer de CartService dependency
     * 
     * @param \App\Services\CartService $cartService
     */
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Haal de huidige winkelwagen inhoud op
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        // Haal cart items en totalen op via de service
        $items = $this->cartService->getItems();
        $totals = $this->cartService->getTotals();

        return response()->json([
            'cartItems' => $items,
            'totals' => $totals,
        ]);
    }

    /**
     * Voeg een item toe aan de winkelwagen
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request): JsonResponse
    {
        try {
            // Valideer inkomende gegevens
            $validated = $request->validate([
                'product_id' => 'required|integer|exists:products,id',
                'quantity' => 'nullable|integer|min:1|max:99',
            ]);

            // Probeer item toe te voegen via service
            $success = $this->cartService->addItem(
                $validated['product_id'],
                $validated['quantity'] ?? 1
            );

            // Controleer of toevoegen is gelukt (bijv. voldoende voorraad)
            if (!$success) {
                return response()->json([
                    'message' => 'Could not add item to cart. Check stock availability.',
                ], 400);
            }

            // Haal bijgewerkte totalen op
            $totals = $this->cartService->getTotals();

            return response()->json([
                'message' => 'Item added to cart successfully',
                'totals' => $totals,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Server error',
            ], 500);
        }
    }

    /**
     * Werk de hoeveelheid van een cart item bij
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $productId): JsonResponse
    {
        try {
            // Valideer nieuwe hoeveelheid (0 = verwijderen)
            $validated = $request->validate([
                'quantity' => 'required|integer|min:0|max:99',
            ]);

            // Probeer hoeveelheid bij te werken via service
            $success = $this->cartService->updateQuantity($productId, $validated['quantity']);

            if (!$success) {
                return response()->json([
                    'message' => 'Could not update cart item.',
                ], 400);
            }

            // Haal bijgewerkte totalen op
            $totals = $this->cartService->getTotals();

            return response()->json([
                'message' => 'Cart updated successfully',
                'totals' => $totals,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Server error',
            ], 500);
        }
    }

    /**
     * Verwijder een item uit de winkelwagen
     * 
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove(int $productId): JsonResponse
    {
        // Probeer item te verwijderen via service
        $success = $this->cartService->removeItem($productId);

        if (!$success) {
            return response()->json([
                'message' => 'Could not remove item from cart.',
            ], 400);
        }

        // Haal bijgewerkte totalen op
        $totals = $this->cartService->getTotals();

        return response()->json([
            'message' => 'Item removed from cart',
            'totals' => $totals,
        ]);
    }

    /**
     * Maak de gehele winkelwagen leeg
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function clear(): JsonResponse
    {
        // Leeg de winkelwagen via service
        $this->cartService->clear();

        return response()->json([
            'message' => 'Cart cleared successfully',
        ]);
    }
}