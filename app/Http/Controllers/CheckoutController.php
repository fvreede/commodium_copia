<?php

namespace App\Http\Controllers;

use App\Models\DeliverySlot;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    private CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Show checkout page with all necessary data
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('message', 'Je moet inloggen om te kunnen afrekenen.');
        }

        if (!$user->isCustomer()) {
            abort(403, 'Je hebt geen toegang tot deze pagina.');
        }

        // Check if cart has items - let frontend handle this via cart store
        $cartItems = $this->cartService->getItems();

        if (empty($cartItems)) {
            return redirect()->route('cart.index');
        }

        // Load the user's delivery address
        $deliveryAddress = $user->address;

        return Inertia::render('CheckoutPage', [
            // Remove cartItems and cartTotal from props - frontend will use store
            'deliverySlots' => $this->getFormattedDeliverySlots(),
            'deliveryAddress' => $deliveryAddress,
            'selectedSlotId' => session('selected_delivery_slot_id'),
            // Add user info for potential address management
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
    }

    /**
     * API endpoint to get current cart data (for real-time updates)
     */
    public function getCartData()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $cartItems = $this->cartService->getItems();
        $totals = $this->cartService->getTotals();

        return response()->json([
            'cartItems' => $this->formatCartItemsForFrontend($cartItems),
            'totals' => $totals,
            'hasItems' => !empty($cartItems)
        ]);
    }

    /**
     * Format cart items with proper image URLs and safety checks
     */
    private function formatCartItemsForFrontend(array $cartItems): array
    {
        return array_map(function ($item) {
            return [
                'id' => $item['id'],
                'product_id' => $item['product_id'],
                'name' => $item['name'],
                'price' => (float) $item['price'],
                'quantity' => (int) $item['quantity'],
                'stock_quantity' => (int) $item['stock_quantity'],
                'image_path' => $this->getImageUrl($item['image_path'] ?? null),
                'short_description' => $item['short_description'] ?? '',
                'is_active' => (bool) $item['is_active'],
                'line_total' => (float) ($item['price'] * $item['quantity'])
            ];
        }, $cartItems);
    }

    /**
     * Get proper image URL with fallback
     */
    private function getImageUrl(?string $imagePath): string
    {
        if (!$imagePath) {
            return asset('images/placeholder.jpg');
        }

        // Check if image exists in storage
        $fullPath = storage_path('app/public/' . $imagePath);
        if (file_exists($fullPath)) {
            return asset('storage/' . $imagePath);
        }

        // Fallback to placeholder
        return asset('images/placeholder.jpg');
    }

    /**
     * Format delivery slots for frontend consumption with proper error handling
     */
    private function getFormattedDeliverySlots()
    {
        try {
            $slots = DeliverySlot::available()->get();
            
            // Group slots by date and format for the frontend
            $grouped = $slots->groupBy('date')->map(function ($daySlots, $date) {
                $carbonDate = Carbon::parse($date);
                
                return [
                    'date' => $date,
                    'day_name' => $carbonDate->format('D'), // Mon, Tue, etc.
                    'formatted_date' => $carbonDate->format('M j'), // Jan 15, etc.
                    'slots' => $daySlots->map(function ($slot) {
                        return [
                            'id' => $slot->id,
                            'time_display' => $slot->start_time . ' - ' . $slot->end_time,
                            'price' => (float) ($slot->price ?? 0), // Ensure price is always a float
                            'available_slots' => (int) ($slot->available_slots ?? 0), // Ensure it's always an integer
                            'current_available' => $slot->getCurrentAvailableSlots(), // Real-time availability
                        ];
                    })->values()
                ];
            })->values();

            return $grouped;
        } catch (\Exception $e) {
            Log::error('Error formatting delivery slots', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return empty array on error to prevent frontend crashes
            return collect([]);
        }
    }

    /**
     * Select delivery slot (AJAX endpoint)
     */
    public function selectDeliverySlot(Request $request)
    {
        $request->validate([
            'delivery_slot_id' => ['required', 'exists:delivery_slots,id'],
        ]);

        try {
            $slot = DeliverySlot::findOrFail($request->delivery_slot_id);
            
            // Check if slot is still available
            if ($slot->getCurrentAvailableSlots() <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dit bezorgmoment is niet meer beschikbaar.'
                ], 422);
            }
            
            // Check if slot date is still valid
            if ($slot->date < today()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dit bezorgmoment is verlopen.'
                ], 422);
            }

            // Store selected slot in session for persistence
            session(['selected_delivery_slot_id' => $request->delivery_slot_id]);

            return response()->json([
                'success' => true,
                'slot' => [
                    'id' => $slot->id,
                    'time_display' => $slot->start_time . ' - ' . $slot->end_time,
                    'price' => (float) $slot->price,
                    'date' => $slot->date
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error selecting delivery slot', [
                'slot_id' => $request->delivery_slot_id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Er is een fout opgetreden bij het selecteren van het bezorgmoment.'
            ], 500);
        }
    }

    /**
     * Show confirmation page
     */
    public function confirm()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $cartItems = $this->cartService->getItems();
        $selectedSlotId = session('selected_delivery_slot_id');

        if (empty($cartItems) || !$selectedSlotId) {
            return redirect()->route('checkout.index');
        }

        $deliverySlot = DeliverySlot::find($selectedSlotId);
        
        // Validate slot is still available
        if (!$deliverySlot || $deliverySlot->getCurrentAvailableSlots() <= 0) {
            session()->forget('selected_delivery_slot_id');
            return redirect()->route('checkout.index')
                ->with('error', 'Het geselecteerde bezorgmoment is niet meer beschikbaar.');
        }

        $deliveryAddress = $user->address;
        $totals = $this->cartService->getTotals();

        return Inertia::render('CheckoutConfirm', [
            'cartItems' => $this->formatCartItemsForFrontend($cartItems),
            'totals' => $totals,
            'deliverySlot' => [
                'id' => $deliverySlot->id,
                'time_display' => $deliverySlot->start_time . ' - ' . $deliverySlot->end_time,
                'price' => (float) $deliverySlot->price,
                'date' => $deliverySlot->date
            ],
            'deliveryAddress' => $deliveryAddress,
        ]);
    }

    /**
     * Handle checkout form submission
     */
    public function store(Request $request)
    {
        $request->validate([
            'delivery_slot_id' => ['required', 'exists:delivery_slots,id'],
        ]);

        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $cartItems = $this->cartService->getItems();

        if (empty($cartItems)) {
            return response()->json([
                'success' => false,
                'message' => 'Je winkelwagen is leeg.'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $deliverySlot = DeliverySlot::findOrFail($request->input('delivery_slot_id'));
            
            // Final availability check
            if ($deliverySlot->getCurrentAvailableSlots() <= 0) {
                throw new \Exception('Bezorgmoment niet meer beschikbaar');
            }

            // Calculate totals
            $totals = $this->cartService->getTotals();
            $deliveryFee = (float) $deliverySlot->price;
            $finalTotal = $totals['subtotal'] + $deliveryFee;

            $order = Order::create([
                'user_id' => $user->id,
                'delivery_slot_id' => $request->input('delivery_slot_id'),
                'status' => 'pending',
                'subtotal' => $totals['subtotal'],
                'delivery_fee' => $deliveryFee,
                'total' => $finalTotal,
                'order_date' => Carbon::now(),
            ]);

            // Create order items and update stock
            foreach ($cartItems as $item) {
                // Double-check stock availability
                $product = \App\Models\Product::find($item['product_id']);
                if (!$product || $product->stock_quantity < $item['quantity']) {
                    throw new \Exception("Product '{$item['name']}' is niet meer voldoende op voorraad.");
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'product_name' => $item['name'], // Store name for historical purposes
                ]);

                // Update product stock
                $product->decrement('stock_quantity', $item['quantity']);
            }

            // Clear cart and session
            $this->cartService->clear();
            session()->forget('selected_delivery_slot_id');

            DB::commit();

            // Return JSON response for AJAX or redirect for regular requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bestelling succesvol geplaatst!',
                    'order_id' => $order->id,
                    'redirect' => route('orders.show', $order->id)
                ]);
            }

            return redirect()->route('orders.show', $order->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
            
            return redirect()->route('checkout.index')
                ->with('error', 'Er is een fout opgetreden bij het plaatsen van uw bestelling: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint for session check (used by frontend)
     */
    public function checkSession()
    {
        $authenticated = Auth::check();
        $timeRemaining = null;

        if ($authenticated) {
            // Laravel session lifetime in minutes (default 120)
            $sessionLifetime = config('session.lifetime') * 60; // Convert to seconds
            $lastActivity = session('_token') ? session()->get('_flash.old', []) : null;

            $timeRemaining = $sessionLifetime;
        }

        return response()->json([
            'authenticated' => $authenticated,
            'time_remaining' => $timeRemaining
        ]);
    }
}