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
use Inertia\Response;

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
            'cartTotal' => $this->cartService->getTotals()['subtotal'], // Match the prop name in Vue
            'deliverySlots' => $this->getFormattedDeliverySlots(),
            'deliveryAddress' => $deliveryAddress,
            'selectedSlotId' => session('selected_delivery_slot_id'), // For persistence
        ]);
    }

    /**
     * Format delivery slots for frontend consumption
     */
    private function getFormattedDeliverySlots()
    {
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
                        'price' => (float) $slot->price,
                    ];
                })->values()
            ];
        })->values();

        return $grouped;
    }

    /**
     * Select delivery slot (AJAX endpoint)
     */
    public function selectSlot(Request $request)
    {
        $request->validate([
            'delivery_slot_id' => ['required', 'exists:delivery_slots,id'],
        ]);

        // Store selected slot in session for persistence
        session(['selected_delivery_slot_id' => $request->delivery_slot_id]);

        return response()->json(['success' => true]);
    }

    /**
     * Show confirmation page
     */
    public function confirm()
    {
        $user = Auth::user();
        $cartItems = $this->cartService->getItems();
        $selectedSlotId = session('selected_delivery_slot_id');

        if (empty($cartItems) || !$selectedSlotId) {
            return Inertia::location(route('checkout.index'));
        }

        $deliverySlot = DeliverySlot::find($selectedSlotId);
        $deliveryAddress = $user->address;

        return Inertia::render('CheckoutConfirm', [
            'cartItems' => $cartItems,
            'cartTotal' => $this->cartService->getTotals()['subtotal'],
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
        $cartItems = $this->cartService->getItems();

        if (empty($cartItems)) {
            return Inertia::location(route('cart.index'));
        }

        DB::beginTransaction();

        try {
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
            session()->forget('selected_delivery_slot_id'); // Clear selected slot

            DB::commit();

            return Inertia::location(route('orders.show', $order->id));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            return Inertia::location(route('cart.index'));
        }
    }
}