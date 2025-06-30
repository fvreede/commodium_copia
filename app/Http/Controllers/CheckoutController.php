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
     * Legacy index method - redirect to step 1
     */
    public function index()
    {
        return redirect()->route('checkout.delivery');
    }

    /**
     * Step 1: Show delivery slot selection page
     */
    public function delivery()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('message', 'Je moet inloggen om te kunnen afrekenen.');
        }

        if (!$user->isCustomer()) {
            abort(403, 'Je hebt geen toegang tot deze pagina.');
        }

        // Check if cart has items
        $cartItems = $this->cartService->getItems();
        if (empty($cartItems)) {
            return redirect()->route('cart.index')
                ->with('error', 'Je winkelwagen is leeg.');
        }

        // Load the user's delivery address
        $deliveryAddress = $user->address;

        return Inertia::render('Checkout/Step1Delivery', [
            'deliverySlots' => $this->getFormattedDeliverySlots(),
            'deliveryAddress' => $deliveryAddress,
            'selectedSlotId' => session('selected_delivery_slot_id'),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
    }

    /**
     * Step 2: Review order details
     */
    public function review()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check if cart has items
        $cartItems = $this->cartService->getItems();
        if (empty($cartItems)) {
            return redirect()->route('cart.index')
                ->with('error', 'Je winkelwagen is leeg.');
        }

        // Check if delivery slot is selected
        $selectedSlotId = session('selected_delivery_slot_id');
        $deliveryAddress = $user->address;

        if (!$selectedSlotId || !$deliveryAddress) {
            return redirect()->route('checkout.delivery')
                ->with('error', 'Selecteer eerst een bezorgmoment en controleer je adresgegevens.');
        }

        // Validate selected slot is still available
        $deliverySlot = DeliverySlot::find($selectedSlotId);
        if (!$deliverySlot || $deliverySlot->getCurrentAvailableSlots() <= 0) {
            session()->forget('selected_delivery_slot_id');
            return redirect()->route('checkout.delivery')
                ->with('error', 'Het geselecteerde bezorgmoment is niet meer beschikbaar.');
        }

        return Inertia::render('Checkout/Step2Review', [
            'deliverySlots' => $this->getFormattedDeliverySlots(),
            'deliveryAddress' => $deliveryAddress,
            'selectedSlotId' => $selectedSlotId,
            'deliveryFee' => (float) $deliverySlot->price,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
    }

    /**
     * Step 3: Final confirmation and payment
     */
    public function confirm()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check if cart has items
        $cartItems = $this->cartService->getItems();
        if (empty($cartItems)) {
            return redirect()->route('cart.index')
                ->with('error', 'Je winkelwagen is leeg.');
        }

        // Check if delivery slot is selected
        $selectedSlotId = session('selected_delivery_slot_id');
        $deliveryAddress = $user->address;

        if (!$selectedSlotId || !$deliveryAddress) {
            return redirect()->route('checkout.delivery')
                ->with('error', 'Controleer je bezorggegevens voordat je verder gaat.');
        }

        // Validate selected slot is still available
        $deliverySlot = DeliverySlot::find($selectedSlotId);
        if (!$deliverySlot || $deliverySlot->getCurrentAvailableSlots() <= 0) {
            session()->forget('selected_delivery_slot_id');
            return redirect()->route('checkout.delivery')
                ->with('error', 'Het geselecteerde bezorgmoment is niet meer beschikbaar.');
        }

        return Inertia::render('Checkout/Step3Confirm', [
            'deliverySlots' => $this->getFormattedDeliverySlots(),
            'deliveryAddress' => $deliveryAddress,
            'selectedSlotId' => $selectedSlotId,
            'deliveryFee' => (float) $deliverySlot->price,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
    }

    /**
     * Store selected delivery slot in session (new method for step persistence)
     */
    public function storeSelectedSlot(Request $request)
    {
        $request->validate([
            'delivery_slot_id' => 'required|exists:delivery_slots,id',
            'delivery_fee' => 'required|numeric|min:0'
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

            // Store in session for persistence across steps
            session([
                'selected_delivery_slot_id' => $request->delivery_slot_id,
                'delivery_fee' => $request->delivery_fee
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bezorgmoment opgeslagen'
            ]);
        } catch (\Exception $e) {
            Log::error('Error storing delivery slot', [
                'slot_id' => $request->delivery_slot_id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Er is een fout opgetreden bij het opslaan van het bezorgmoment.'
            ], 500);
        }
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
     * Select delivery slot (AJAX endpoint) - Legacy method, kept for compatibility
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
     * Handle checkout form submission (final order processing)
     * ENHANCED VERSION with order number generation and better status handling
     */
    public function store(Request $request)
    {
        $request->validate([
            'delivery_slot_id' => ['required', 'exists:delivery_slots,id'],
            'order_notes' => 'nullable|string|max:500',
            'payment_method' => 'required|in:ideal,card,cash'
        ]);

        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Je bent niet ingelogd.'
            ], 401);
        }

        $cartItems = $this->cartService->getItems();

        if (empty($cartItems)) {
            return response()->json([
                'success' => false,
                'message' => 'Je winkelwagen is leeg.'
            ], 422);
        }

        // Validate delivery address
        if (!$user->address) {
            return response()->json([
                'success' => false,
                'message' => 'Geen geldig bezorgadres gevonden.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $deliverySlot = DeliverySlot::findOrFail($request->input('delivery_slot_id'));
            
            // Final availability check
            if ($deliverySlot->getCurrentAvailableSlots() <= 0) {
                throw new \Exception('Het geselecteerde bezorgmoment is niet meer beschikbaar.');
            }

            // Calculate totals
            $totals = $this->cartService->getTotals();
            $deliveryFee = (float) $deliverySlot->price;
            $finalTotal = $totals['subtotal'] + $deliveryFee;

            // Generate unique order number
            $orderNumber = $this->generateOrderNumber();

            // Create order with enhanced data
            $order = Order::create([
                'user_id' => $user->id,
                'delivery_slot_id' => $request->input('delivery_slot_id'),
                'order_number' => $orderNumber,
                'status' => 'confirmed', // Since no payment needed, mark as confirmed
                'subtotal' => $totals['subtotal'],
                'delivery_fee' => $deliveryFee,
                'total' => $finalTotal,
                'payment_method' => $request->payment_method,
                'payment_status' => 'completed', // No actual payment needed
                'delivery_address' => $user->address->toArray(),
                'order_notes' => $request->order_notes,
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
                    'total' => $item['price'] * $item['quantity'],
                    'product_name' => $item['name'], // Store name for historical purposes
                ]);

                // Update product stock
                $product->decrement('stock_quantity', $item['quantity']);
            }

            // Update delivery slot availability
            $deliverySlot->decrement('available_slots', 1);

            // Clear cart and session
            $this->cartService->clear();
            session()->forget(['selected_delivery_slot_id', 'delivery_fee']);

            DB::commit();

            // Log successful order
            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => $user->id,
                'total' => $finalTotal
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bestelling succesvol geplaatst!',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'redirect' => route('checkout.success', $order->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'delivery_slot_id' => $request->input('delivery_slot_id'),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Show order success page
     */
    public function success(Order $order)
    {
        // Ensure user can only see their own orders
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Je hebt geen toegang tot deze bestelling.');
        }

        // Load order with relationships
        $order->load(['items.product', 'deliverySlot', 'user']);

        return Inertia::render('Checkout/OrderSuccess', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'subtotal' => $order->subtotal,
                'delivery_fee' => $order->delivery_fee,
                'total_amount' => $order->total,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status ?? 'completed',
                'order_notes' => $order->order_notes,
                'created_at' => $order->created_at,
            ],
            'orderItems' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->price,
                    'product' => [
                        'name' => $item->product_name ?? $item->product?->name ?? 'Product niet gevonden',
                        'image_url' => $item->product?->image_path ? asset('storage/' . $item->product->image_path) : null,
                    ]
                ];
            }),
            'deliveryAddress' => $order->delivery_address,
            'deliverySlot' => $order->deliverySlot ? [
                'delivery_date' => $order->deliverySlot->date->format('Y-m-d'),
                'time_start' => $order->deliverySlot->start_time,
                'time_end' => $order->deliverySlot->end_time,
            ] : null,
            'user' => [
                'name' => $order->user->name,
                'email' => $order->user->email,
            ]
        ]);
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber(): string
    {
        do {
            // Format: CC-YYYY-NNNNNN (Commodum Copia - Year - 6-digit number)
            $orderNumber = 'CC-' . date('Y') . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
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
                    'day_name' => $carbonDate->translatedFormat('l'), // Monday, Tuesday, etc. (localized)
                    'formatted_date' => $carbonDate->translatedFormat('j M'), // 15 Jan (localized)
                    'slots' => $daySlots->map(function ($slot) {
                        return [
                            'id' => $slot->id,
                            'start_time' => $slot->start_time,
                            'end_time' => $slot->end_time,
                            'time_display' => Carbon::parse($slot->start_time)->format('H:i') . ' - ' . Carbon::parse($slot->end_time)->format('H:i'),
                            'price' => (float) ($slot->price ?? 0), // Ensure price is always a float
                            'available_slots' => (int) ($slot->available_slots ?? 0), // Ensure it's always an integer
                            'current_available' => $slot->getCurrentAvailableSlots(), // Real-time availability
                            'last_updated' => $slot->updated_at
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
     * API endpoint for session check (used by frontend)
     */
    public function checkSession()
    {
        $authenticated = Auth::check();
        $timeRemaining = null;

        if ($authenticated) {
            // Laravel session lifetime in minutes (default 120)
            $sessionLifetime = config('session.lifetime') * 60; // Convert to seconds
            $timeRemaining = $sessionLifetime;
        }

        return response()->json([
            'authenticated' => $authenticated,
            'time_remaining' => $timeRemaining
        ]);
    }
}