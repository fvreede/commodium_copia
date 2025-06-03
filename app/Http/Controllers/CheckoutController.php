<?php

namespace App\Http\Controllers;

use App\Models\DeliverySlot;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    public function index()
    {
        // Get delivery slots for the next 7 days, grouped by date
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
                    'day_name' => Carbon::parse($date)->locale('nl')->format('l'),
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

        // Get user's current address (you might want to expand this)
        $user = auth()->user();
        $deliveryAddress = [
            'street' => $user->street ?? 'Voorbeeldstraat 123',
            'city' => $user->city ?? 'Haarlem',
            'postal_code' => $user->postal_code ?? '2000 AB',
            'country' => $user->country ?? 'Nederland'
        ];

        return Inertia::render('CheckoutPage', [
            'deliverySlots' => $deliverySlots,
            'deliveryAddress' => $deliveryAddress,
            'cartItems' => [], // Empty for now, will be implemented in assignment 4
            'cartTotal' => 0.00
        ]);
    }

    public function selectDeliverySlot(Request $request)
    {
        $request->validate([
            'delivery_slot_id' => 'required|exists:delivery_slots,id'
        ]);

        // Store selected delivery slot in session for now
        // In assignment 4, you'll probably store this in the cart or create an order
        session(['selected_delivery_slot_id' => $request->delivery_slot_id]);

        return redirect()->route('checkout.confirm')
            ->with('success', 'Bezorgmoment geselecteerd!');
    }
}