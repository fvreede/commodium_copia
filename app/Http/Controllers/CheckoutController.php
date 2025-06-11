<?php

namespace App\Http\Controllers;

use App\Models\DeliverySlot;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Services\CartService;

class CheckoutController extends Controller
{
    public function index(CartService $cartService)
    {
        // Haal bezorgmomenten uit de database (komende 7 dagen)
        $deliverySlots = DeliverySlot::where('date', '>=', Carbon::today())
            ->where('date', '<=', Carbon::today()->addDays(6))
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->groupBy(function ($slot) {
                return $slot->date->format('Y-m-d');
            })
            ->map(function ($daySlots, $date) {
                return [
                    'date' => Carbon::parse($date)->format('Y-m-d'),
                    'day_name' => Carbon::parse($date)->locale('nl')->isoFormat('dddd'),
                    'formatted_date' => Carbon::parse($date)->format('d-m'),
                    'slots' => $daySlots->map(function ($slot) {
                        return [
                            'id' => $slot->id,
                            'start_time' => $slot->start_time,
                            'end_time' => $slot->end_time,
                            'price' => (float) $slot->price,
                            'time_display' => $slot->start_time . '-' . $slot->end_time,
                        ];
                    })->values()
                ];
            })
            ->values();

        $user = auth()->user();
        $deliveryAddress = $user->address()->first();

        return Inertia::render('CheckoutPage', [
            'deliverySlots' => $deliverySlots,
            'deliveryAddress' => $deliveryAddress,
            'cartItems' => $cartService->getItems(),
            'cartTotal' => $cartService->getTotals(),
            'selectedSlotId' => session('selected_delivery_slot_id', null)
        ]);
    }

    public function selectDeliverySlot(Request $request)
    {
        $request->validate([
            'delivery_slot_id' => 'required|exists:delivery_slots,id'
        ]);

        session(['selected_delivery_slot_id' => $request->delivery_slot_id]);

        return redirect()->route('checkout.confirm')
            ->with('success', 'Bezorgmoment geselecteerd!');
    }
}
