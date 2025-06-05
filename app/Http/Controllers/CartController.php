<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Get cart contents
     */
    public function index(): JsonResponse
    {
        $items = $this->cartService->getItems();
        $totals = $this->cartService->getTotals();

        return response()->json([
            'cartItems' => $items,
            'totals' => $totals,
        ]);
    }

    /**
     * Add item to cart
     */
    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'integer|min:1|max:99',
        ]);

        $success = $this->cartService->addItem(
            $request->product_id,
            $request->quantity ?? 1
        );

        if (!$success) {
            return response()->json([
                'message' => 'Could not add item to cart. Check stock availability.',
            ], 400);
        }

        $totals = $this->cartService->getTotals();

        return response()->json([
            'message' => 'Item added to cart successfully',
            'totals' => $totals,
        ]);
    }

    /**
     * Update item quantity
     */
    public function update(Request $request, int $productId): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:0|max:99',
        ]);

        $success = $this->cartService->updateQuantity($productId, $request->quantity);

        if (!$success) {
            return response()->json([
                'message' => 'Could not update cart item.',
            ], 400);
        }

        $totals = $this->cartService->getTotals();

        return response()->json([
            'message' => 'Cart updated successfully',
            'totals' => $totals,
        ]);
    }

    /**
     * Remove item from cart
     */
    public function remove(int $productId): JsonResponse
    {
        $success = $this->cartService->removeItem($productId);

        if (!$success) {
            return response()->json([
                'message' => 'Could not remove item from cart.',
            ], 400);
        }

        $totals = $this->cartService->getTotals();

        return response()->json([
            'message' => 'Item removed from cart',
            'totals' => $totals,
        ]);
    }

    /**
     * Clear entire cart
     */
    public function clear(): JsonResponse
    {
        $this->cartService->clear();

        return response()->json([
            'message' => 'Cart cleared successfully',
        ]);
    }
}