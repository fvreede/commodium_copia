<?php

/**
 * Bestandsnaam: CheckoutController.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.9
 * Datum: 2025-07-01
 * Tijd: 01:25:04
 * Doel: Controller voor het complete afrekproces. Behandelt een meersstaps checkout flow inclusief bezorgslot selectie, bestelling validatie, order aanmaak en betalingsafhandeling.
 */

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
     * Legacy index methode - redirect naar stap 1
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        return redirect()->route('checkout.delivery');
    }

    /**
     * Stap 1: Toon bezorgslot selectie pagina
     * 
     * @return \Inertia\Response|\Illuminate\Http\RedirectResponse
     */
    public function delivery()
    {
        $user = Auth::user();

        // Controleer of gebruiker is ingelogd
        if (!$user) {
            return redirect()->route('login')->with('message', 'Je moet inloggen om te kunnen afrekenen.');
        }

        // Controleer of gebruiker customer rechten heeft
        if (!$user->isCustomer()) {
            abort(403, 'Je hebt geen toegang tot deze pagina.');
        }

        // Controleer of winkelwagen items bevat
        $cartItems = $this->cartService->getItems();
        if (empty($cartItems)) {
            return redirect()->route('cart.index')
                ->with('error', 'Je winkelwagen is leeg.');
        }

        // Laad het bezorgadres van de gebruiker
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
     * Stap 2: Controleer bestelling details
     * 
     * @return \Inertia\Response|\Illuminate\Http\RedirectResponse
     */
    public function review()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Controleer of winkelwagen items bevat
        $cartItems = $this->cartService->getItems();
        if (empty($cartItems)) {
            return redirect()->route('cart.index')
                ->with('error', 'Je winkelwagen is leeg.');
        }

        // Controleer of bezorgslot is geselecteerd
        $selectedSlotId = session('selected_delivery_slot_id');
        $deliveryAddress = $user->address;

        if (!$selectedSlotId || !$deliveryAddress) {
            return redirect()->route('checkout.delivery')
                ->with('error', 'Selecteer eerst een bezorgmoment en controleer je adresgegevens.');
        }

        // Valideer dat geselecteerde slot nog beschikbaar is
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
     * Stap 3: Finale bevestiging en betaling
     * 
     * @return \Inertia\Response|\Illuminate\Http\RedirectResponse
     */
    public function confirm()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Controleer of winkelwagen items bevat
        $cartItems = $this->cartService->getItems();
        if (empty($cartItems)) {
            return redirect()->route('cart.index')
                ->with('error', 'Je winkelwagen is leeg.');
        }

        // Controleer of bezorgslot is geselecteerd
        $selectedSlotId = session('selected_delivery_slot_id');
        $deliveryAddress = $user->address;

        if (!$selectedSlotId || !$deliveryAddress) {
            return redirect()->route('checkout.delivery')
                ->with('error', 'Controleer je bezorggegevens voordat je verder gaat.');
        }

        // Valideer dat geselecteerde slot nog beschikbaar is
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
     * Sla geselecteerde bezorgslot op in sessie (nieuwe methode voor stap persistentie)
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeSelectedSlot(Request $request)
    {
        // Valideer inkomende gegevens
        $request->validate([
            'delivery_slot_id' => 'required|exists:delivery_slots,id',
            'delivery_fee' => 'required|numeric|min:0'
        ]);

        try {
            $slot = DeliverySlot::findOrFail($request->delivery_slot_id);

            // Controleer of slot nog beschikbaar is
            if ($slot->getCurrentAvailableSlots() <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dit bezorgmoment is niet meer beschikbaar.'
                ], 422);
            }

            // Controleer of slot datum nog geldig is
            if ($slot->date < today()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dit bezorgmoment is verlopen.'
                ], 422);
            }

            // Sla op in sessie voor persistentie tussen stappen
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
     * API endpoint om huidige cart data op te halen (voor real-time updates)
     * 
     * @return \Illuminate\Http\JsonResponse
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
     * Selecteer bezorgslot (AJAX endpoint) - Legacy methode, behouden voor compatibiliteit
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function selectDeliverySlot(Request $request)
    {
        $request->validate([
            'delivery_slot_id' => ['required', 'exists:delivery_slots,id'],
        ]);

        try {
            $slot = DeliverySlot::findOrFail($request->delivery_slot_id);

            // Controleer of slot nog beschikbaar is
            if ($slot->getCurrentAvailableSlots() <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dit bezorgmoment is niet meer beschikbaar.'
                ], 422);
            }

            // Controleer of slot datum nog geldig is
            if ($slot->date < today()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dit bezorgmoment is verlopen.'
                ], 422);
            }

            // Sla geselecteerde slot op in sessie voor persistentie
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
     * Verwerk checkout formulier (finale bestelling verwerking)
     * UITGEBREIDE VERSIE met bestelnummer generatie en betere status behandeling
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Valideer finale checkout gegevens
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

        // Controleer winkelwagen inhoud
        $cartItems = $this->cartService->getItems();
        if (empty($cartItems)) {
            return response()->json([
                'success' => false,
                'message' => 'Je winkelwagen is leeg.'
            ], 422);
        }

        // Valideer bezorgadres
        if (!$user->address) {
            return response()->json([
                'success' => false,
                'message' => 'Geen geldig bezorgadres gevonden.'
            ], 422);
        }

        // Start database transactie voor consistente data
        DB::beginTransaction();

        try {
            $deliverySlot = DeliverySlot::findOrFail($request->input('delivery_slot_id'));

            // Finale beschikbaarheidscontrole
            if ($deliverySlot->getCurrentAvailableSlots() <= 0) {
                throw new \Exception('Het geselecteerde bezorgmoment is niet meer beschikbaar.');
            }

            // Bereken totalen
            $totals = $this->cartService->getTotals();
            $deliveryFee = (float) $deliverySlot->price;
            $finalTotal = $totals['subtotal'] + $deliveryFee;

            // Genereer uniek bestelnummer
            $orderNumber = $this->generateOrderNumber();

            // Maak bestelling aan met uitgebreide data
            $order = Order::create([
                'user_id' => $user->id,
                'delivery_slot_id' => $request->input('delivery_slot_id'),
                'order_number' => $orderNumber,
                'status' => 'confirmed', // Geen betaling nodig, markeer als bevestigd
                'subtotal' => $totals['subtotal'],
                'delivery_fee' => $deliveryFee,
                'total' => $finalTotal,
                'payment_method' => $request->payment_method,
                'payment_status' => 'completed', // Geen echte betaling nodig
                'delivery_address' => $user->address->toArray(),
                'order_notes' => $request->order_notes,
                'order_date' => Carbon::now(),
            ]);

            // Maak order items aan en werk voorraad bij
            foreach ($cartItems as $item) {
                // Dubbele controle voorraad beschikbaarheid
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
                    'product_name' => $item['name'], // Sla naam op voor historische doeleinden
                ]);

                // Werk product voorraad bij
                $product->decrement('stock_quantity', $item['quantity']);
            }

            // Werk bezorgslot beschikbaarheid bij
            $deliverySlot->decrement('available_slots', 1);

            // Leeg winkelwagen en sessie
            $this->cartService->clear();
            session()->forget(['selected_delivery_slot_id', 'delivery_fee']);

            DB::commit();

            // Log succesvolle bestelling
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
     * Toon bestelling succes pagina
     * 
     * @param \App\Models\Order $order
     * @return \Inertia\Response
     */
    public function success(Order $order)
    {
        // Zorg ervoor dat gebruiker alleen eigen bestellingen kan zien
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Je hebt geen toegang tot deze bestelling.');
        }

        // Laad bestelling met relaties
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
     * Genereer uniek bestelnummer
     * 
     * @return string
     */
    private function generateOrderNumber(): string
    {
        do {
            // Formaat: CC-YYYY-NNNNNN (Commodum Copia - Jaar - 6-cijferig nummer)
            $orderNumber = 'CC-' . date('Y') . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Formatteer cart items met juiste afbeelding URLs en veiligheidscontroles
     * 
     * @param array $cartItems
     * @return array
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
     * Krijg juiste afbeelding URL met fallback
     * 
     * @param string|null $imagePath
     * @return string
     */
    private function getImageUrl(?string $imagePath): string
    {
        if (!$imagePath) {
            return asset('images/placeholder.jpg');
        }

        // Controleer of afbeelding bestaat in storage
        $fullPath = storage_path('app/public/' . $imagePath);
        if (file_exists($fullPath)) {
            return asset('storage/' . $imagePath);
        }

        // Fallback naar placeholder
        return asset('images/placeholder.jpg');
    }

    /**
     * Formatteer bezorgslots voor frontend consumptie met juiste error handling
     * 
     * @return \Illuminate\Support\Collection
     */
    private function getFormattedDeliverySlots()
    {
        try {
            $slots = DeliverySlot::available()->get();

            // Groepeer slots per datum en formatteer voor frontend
            $grouped = $slots->groupBy('date')->map(function ($daySlots, $date) {
                $carbonDate = Carbon::parse($date);

                return [
                    'date' => $date,
                    'day_name' => $carbonDate->translatedFormat('l'), // Maandag, Dinsdag, etc. (gelokaliseerd)
                    'formatted_date' => $carbonDate->translatedFormat('j M'), // 15 Jan (gelokaliseerd)
                    'slots' => $daySlots->map(function ($slot) {
                        return [
                            'id' => $slot->id,
                            'start_time' => $slot->start_time,
                            'end_time' => $slot->end_time,
                            'time_display' => Carbon::parse($slot->start_time)->format('H:i') . ' - ' . Carbon::parse($slot->end_time)->format('H:i'),
                            'price' => (float) ($slot->price ?? 0), // Zorg ervoor dat prijs altijd een float is
                            'available_slots' => (int) ($slot->available_slots ?? 0), // Zorg ervoor dat het altijd een integer is
                            'current_available' => $slot->getCurrentAvailableSlots(), // Real-time beschikbaarheid
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

            // Return lege array bij fout om frontend crashes te voorkomen
            return collect([]);
        }
    }

    /**
     * API endpoint voor sessie controle (gebruikt door frontend)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkSession()
    {
        $authenticated = Auth::check();
        $timeRemaining = null;

        if ($authenticated) {
            // Laravel sessie levensduur in minuten (standaard 120)
            $sessionLifetime = config('session.lifetime') * 60; // Converteer naar seconden
            $timeRemaining = $sessionLifetime;
        }

        return response()->json([
            'authenticated' => $authenticated,
            'time_remaining' => $timeRemaining
        ]);
    }
}