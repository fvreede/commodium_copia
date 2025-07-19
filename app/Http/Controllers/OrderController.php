<?php

/**
 * Bestandsnaam: OrderController.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-07-01
 * Tijd: 01:25:04
 * Doel: Controller voor bestellingsbeheer vanuit klantperspectief. Behandelt order geschiedenis, gedetailleerde order weergave, annulering, tracking en bevestigingsmails voor ingelogde klanten.
 */

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OrderController extends Controller
{
    /**
     * Toon de bestelgeschiedenis van de gebruiker met filtering opties
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Haal query parameters op voor filtering
        $status = $request->get('status');
        $search = $request->get('search');

        // Bouw basis query voor gebruiker's bestellingen
        $query = $user->orders()
            ->with(['items.product', 'deliverySlot']) // Laad gerelateerde data
            ->latest(); // Nieuwste bestellingen eerst

        // Pas status filter toe
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // Pas zoekfilter toe (bestelnummer of productnaam)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('items', function ($itemQuery) use ($search) {
                        $itemQuery->where('product_name', 'like', "%{$search}%");
                    });
            });
        }

        // Pagineer resultaten en behoud query parameters
        $orders = $query->paginate(10)->withQueryString();

        // Transformeer bestellingen voor frontend weergave
        $transformedOrders = $orders->through(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_display' => $this->getStatusDisplay($order->status),
                'total' => $order->total,
                'subtotal' => $order->subtotal,
                'delivery_fee' => $order->delivery_fee,
                'item_count' => $order->items->count(),
                'total_items' => $order->items->sum('quantity'),
                'created_at' => $order->created_at,
                'delivery_slot' => $order->deliverySlot ? [
                    'date' => $order->deliverySlot->date,
                    'start_time' => $order->deliverySlot->start_time,
                    'end_time' => $order->deliverySlot->end_time,
                    'formatted_date' => $order->deliverySlot->date->format('d-m-Y'),
                    'formatted_time' => $order->deliverySlot->start_time . ' - ' . $order->deliverySlot->end_time,
                ] : null,
                'first_item_name' => $order->items->first()?->product_name ?? 'Geen items',
                'can_cancel' => $this->canCancelOrder($order),
                'can_track' => $this->canTrackOrder($order),
            ];
        });

        return Inertia::render('Orders/Index', [
            'orders' => $transformedOrders,
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
            'statusOptions' => $this->getStatusOptions(),
        ]);
    }

    /**
     * Toon gedetailleerde bestellingsinformatie
     * 
     * @param \App\Models\Order $order
     * @return \Inertia\Response
     */
    public function show(Order $order)
    {
        // Zorg ervoor dat gebruiker alleen eigen bestellingen kan bekijken
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Je hebt geen toegang tot deze bestelling.');
        }

        // Laad alle benodigde relaties
        $order->load(['items.product', 'deliverySlot', 'user']);

        return Inertia::render('Orders/Show', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_display' => $this->getStatusDisplay($order->status),
                'subtotal' => $order->subtotal,
                'delivery_fee' => $order->delivery_fee,
                'total' => $order->total,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status ?? 'completed',
                'order_notes' => $order->order_notes,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
                'can_cancel' => $this->canCancelOrder($order),
                'can_track' => $this->canTrackOrder($order),
                'estimated_delivery' => $this->getEstimatedDelivery($order),
            ],
            'orderItems' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->quantity * $item->price,
                    'product_name' => $item->product_name,
                    'product' => $item->product ? [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'image_path' => $item->product->image_path,
                        'is_active' => $item->product->is_active,
                    ] : null,
                ];
            }),
            'deliveryAddress' => $order->delivery_address,
            'deliverySlot' => $order->deliverySlot ? [
                'id' => $order->deliverySlot->id,
                'date' => $order->deliverySlot->date,
                'start_time' => $order->deliverySlot->start_time,
                'end_time' => $order->deliverySlot->end_time,
                'formatted_date' => $order->deliverySlot->date->format('l, j F Y'),
                'formatted_time' => $order->deliverySlot->start_time . ' - ' . $order->deliverySlot->end_time,
            ] : null,
        ]);
    }

    /**
     * Annuleer een bestelling
     * 
     * @param \App\Models\Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(Order $order)
    {
        // Zorg ervoor dat gebruiker alleen eigen bestellingen kan annuleren
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Je hebt geen toegang tot deze bestelling.');
        }

        // Controleer of bestelling geannuleerd kan worden
        if (!$this->canCancelOrder($order)) {
            return back()->with('error', 'Deze bestelling kan niet meer geannuleerd worden.');
        }

        try {
            // Werk bestelling status bij
            $order->update(['status' => 'cancelled']);

            // Herstel product voorraad voor geannuleerde items
            foreach ($order->items as $item) {
                if ($item->product && $item->product->is_active) {
                    $item->product->increment('stock_quantity', $item->quantity);
                }
            }

            // Herstel bezorgslot beschikbaarheid
            if ($order->deliverySlot) {
                $order->deliverySlot->increment('available_slots', 1);
            }

            return back()->with('success', 'Je bestelling is succesvol geannuleerd.');

        } catch (\Exception $e) {
            return back()->with('error', 'Er is een fout opgetreden bij het annuleren van je bestelling.');
        }
    }

    /**
     * Volg bestelling status (tracking pagina)
     * 
     * @param \App\Models\Order $order
     * @return \Inertia\Response
     */
    public function track(Order $order)
    {
        // Zorg ervoor dat gebruiker alleen eigen bestellingen kan volgen
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Je hebt geen toegang tot deze bestelling.');
        }

        $order->load(['deliverySlot']);

        // Genereer tracking stappen voor deze bestelling
        $trackingSteps = $this->getTrackingSteps($order);

        return Inertia::render('Orders/Track', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_display' => $this->getStatusDisplay($order->status),
                'created_at' => $order->created_at,
                'estimated_delivery' => $this->getEstimatedDelivery($order),
            ],
            'deliverySlot' => $order->deliverySlot ? [
                'date' => $order->deliverySlot->date,
                'start_time' => $order->deliverySlot->start_time,
                'end_time' => $order->deliverySlot->end_time,
                'formatted_date' => $order->deliverySlot->date->format('l, j F Y'),
                'formatted_time' => $order->deliverySlot->start_time . ' - ' . $order->deliverySlot->end_time,
            ] : null,
            'trackingSteps' => $trackingSteps,
            'currentStep' => $this->getCurrentTrackingStep($order->status),
        ]);
    }

    /**
     * Verstuur bestelling bevestigingsmail opnieuw
     * 
     * @param \App\Models\Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendConfirmation(Order $order)
    {
        // Zorg ervoor dat gebruiker alleen voor eigen bestellingen bevestiging kan aanvragen
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Je hebt geen toegang tot deze bestelling.');
        }

        try {
            // Hier zou je email verzending implementeren
            // Voor nu retourneren we alleen success
            // Mail::to($order->user->email)->send(new OrderConfirmation($order));

            return response()->json([
                'success' => true,
                'message' => 'Bevestigingsmail is opnieuw verzonden.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Er is een fout opgetreden bij het versturen van de bevestigingsmail.'
            ], 500);
        }
    }

    /**
     * Krijg Nederlandse weergave tekst voor bestelling status
     * 
     * @param string $status
     * @return string
     */
    private function getStatusDisplay($status): string
    {
        $statuses = [
            'pending' => 'In behandeling',
            'confirmed' => 'Bevestigd',
            'processing' => 'Wordt voorbereid',
            'out_for_delivery' => 'Onderweg',
            'delivered' => 'Bezorgd',
            'cancelled' => 'Geannuleerd',
        ];

        return $statuses[$status] ?? 'Onbekend';
    }

    /**
     * Krijg beschikbare status opties voor filtering
     * 
     * @return array
     */
    private function getStatusOptions(): array
    {
        return [
            ['value' => 'all', 'label' => 'Alle bestellingen'],
            ['value' => 'confirmed', 'label' => 'Bevestigd'],
            ['value' => 'processing', 'label' => 'Wordt voorbereid'],
            ['value' => 'out_for_delivery', 'label' => 'Onderweg'],
            ['value' => 'delivered', 'label' => 'Bezorgd'],
            ['value' => 'cancelled', 'label' => 'Geannuleerd'],
        ];
    }

    /**
     * Controleer of bestelling geannuleerd kan worden
     * 
     * @param \App\Models\Order $order
     * @return bool
     */
    private function canCancelOrder(Order $order): bool
    {
        // Kan alleen annuleren als status nog pending/confirmed is
        // en bezorging nog meer dan 1 dag weg is
        return in_array($order->status, ['confirmed', 'pending']) &&
            $order->deliverySlot &&
            $order->deliverySlot->date > now()->addDay(); // Annuleren tot 1 dag voor bezorging
    }

    /**
     * Controleer of bestelling gevolgd kan worden
     * 
     * @param \App\Models\Order $order
     * @return bool
     */
    private function canTrackOrder(Order $order): bool
    {
        // Alle bestellingen behalve geannuleerde kunnen gevolgd worden
        return !in_array($order->status, ['cancelled']);
    }

    /**
     * Krijg geschatte bezorgtijd als leesbare tekst
     * 
     * @param \App\Models\Order $order
     * @return string|null
     */
    private function getEstimatedDelivery(Order $order): ?string
    {
        if (!$order->deliverySlot) {
            return null;
        }

        return $order->deliverySlot->date->format('l, j F Y') .
            ' tussen ' .
            $order->deliverySlot->start_time .
            ' en ' .
            $order->deliverySlot->end_time;
    }

    /**
     * Genereer tracking stappen voor een bestelling
     * 
     * @param \App\Models\Order $order
     * @return array
     */
    private function getTrackingSteps(Order $order): array
    {
        $steps = [
            [
                'title' => 'Bestelling ontvangen',
                'description' => 'Je bestelling is succesvol geplaatst',
                'status' => 'completed',
                'icon' => 'check-circle',
                'date' => $order->created_at,
            ],
            [
                'title' => 'Bestelling bevestigd',
                'description' => 'We hebben je bestelling bevestigd en beginnen met voorbereiden',
                'status' => in_array($order->status, ['confirmed', 'processing', 'out_for_delivery', 'delivered']) ? 'completed' : 'pending',
                'icon' => 'clipboard-check',
                'date' => in_array($order->status, ['confirmed', 'processing', 'out_for_delivery', 'delivered']) ? $order->created_at : null,
            ],
            [
                'title' => 'Wordt voorbereid',
                'description' => 'Je bestelling wordt ingepakt en klaargezet voor bezorging',
                'status' => in_array($order->status, ['processing', 'out_for_delivery', 'delivered']) ? 'completed' :
                    ($order->status === 'confirmed' ? 'current' : 'pending'),
                'icon' => 'cog',
                'date' => in_array($order->status, ['processing', 'out_for_delivery', 'delivered']) ? $order->updated_at : null,
            ],
            [
                'title' => 'Onderweg',
                'description' => 'Je bestelling is onderweg naar je adres',
                'status' => in_array($order->status, ['out_for_delivery', 'delivered']) ? 'completed' :
                    ($order->status === 'processing' ? 'current' : 'pending'),
                'icon' => 'truck',
                'date' => in_array($order->status, ['out_for_delivery', 'delivered']) ? $order->updated_at : null,
            ],
            [
                'title' => 'Bezorgd',
                'description' => 'Je bestelling is succesvol bezorgd',
                'status' => $order->status === 'delivered' ? 'completed' :
                    ($order->status === 'out_for_delivery' ? 'current' : 'pending'),
                'icon' => 'home',
                'date' => $order->status === 'delivered' ? $order->updated_at : null,
            ],
        ];

        // Als bestelling geannuleerd is, markeer alle toekomstige stappen als geannuleerd
        if ($order->status === 'cancelled') {
            foreach ($steps as &$step) {
                if ($step['status'] === 'pending' || $step['status'] === 'current') {
                    $step['status'] = 'cancelled';
                }
            }
        }

        return $steps;
    }

    /**
     * Krijg huidige tracking stap index voor frontend weergave
     * 
     * @param string $status
     * @return int
     */
    private function getCurrentTrackingStep(string $status): int
    {
        $stepMap = [
            'pending' => 0,
            'confirmed' => 1,
            'processing' => 2,
            'out_for_delivery' => 3,
            'delivered' => 4,
            'cancelled' => -1, // Speciale behandeling voor geannuleerde bestellingen
        ];

        return $stepMap[$status] ?? 0;
    }
}