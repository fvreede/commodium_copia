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

        $cartItems = $this->cartService->getItems();

        if (empty($cartItems)) {
            return Inertia::location(route('cart.index'));
        }

        // Load the user's delivery address
        $deliveryAddress = $user->address;

        return Inertia::render('CheckoutPage', [
            'cartItems' => $cartItems,
            'cartTotal' => (float) $this->cartService->getTotals()['subtotal'], // Ensure it's a float
            'deliverySlots' => $this->getFormattedDeliverySlots(),
            'deliveryAddress' => $deliveryAddress,
            'selectedSlotId' => session('selected_delivery_slot_id'),
        ]);
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

            return response()->json(['success' => true]);
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
            return Inertia::location(route('checkout.index'));
        }

        $deliverySlot = DeliverySlot::find($selectedSlotId);
        
        // Validate slot is still available
        if (!$deliverySlot || $deliverySlot->getCurrentAvailableSlots() <= 0) {
            session()->forget('selected_delivery_slot_id');
            return Inertia::location(route('checkout.index'))
                ->with('error', 'Het geselecteerde bezorgmoment is niet meer beschikbaar.');
        }

        $deliveryAddress = $user->address;

        return Inertia::render('CheckoutConfirm', [
            'cartItems' => $cartItems,
            'cartTotal' => (float) $this->cartService->getTotals()['subtotal'], // Ensure it's a float
            'deliverySlot' => $deliverySlot,
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
            return Inertia::location(route('cart.index'));
        }

        DB::beginTransaction();

        try {
            $deliverySlot = DeliverySlot::findOrFail($request->input('delivery_slot_id'));
            
            // Final availability check
            if ($deliverySlot->getCurrentAvailableSlots() <= 0) {
                throw new \Exception('Bezorgmoment niet meer beschikbaar');
            }

            $order = Order::create([
                'user_id' => $user->id,
                'delivery_slot_id' => $request->input('delivery_slot_id'),
                'status' => 'pending',
                'total_price' => $this->cartService->getTotals()['total'],
                'order_date' => Carbon::now(),
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            $this->cartService->clear();
            session()->forget('selected_delivery_slot_id');

            DB::commit();

            return Inertia::location(route('orders.show', $order->id));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            
            return redirect()->route('checkout.index')
                ->with('error', 'Er is een fout opgetreden bij het plaatsen van uw bestelling.');
        }
    }
}