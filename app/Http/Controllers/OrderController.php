<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OrderController extends Controller
{
    /**
     * Display the user's order history
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get query parameters for filtering
        $status = $request->get('status');
        $search = $request->get('search');
        
        // Build query
        $query = $user->orders()
            ->with(['items.product', 'deliverySlot'])
            ->latest();
        
        // Apply filters
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('items', function ($itemQuery) use ($search) {
                      $itemQuery->where('product_name', 'like', "%{$search}%");
                  });
            });
        }
        
        $orders = $query->paginate(10)->withQueryString();
        
        // Transform orders for frontend
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
     * Show detailed order information
     */
    public function show(Order $order)
    {
        // Ensure user can only view their own orders
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Je hebt geen toegang tot deze bestelling.');
        }

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
     * Cancel an order
     */
    public function cancel(Order $order)
    {
        // Ensure user can only cancel their own orders
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Je hebt geen toegang tot deze bestelling.');
        }

        if (!$this->canCancelOrder($order)) {
            return back()->with('error', 'Deze bestelling kan niet meer geannuleerd worden.');
        }

        try {
            // Update order status
            $order->update(['status' => 'cancelled']);

            // Restore product stock
            foreach ($order->items as $item) {
                if ($item->product && $item->product->is_active) {
                    $item->product->increment('stock_quantity', $item->quantity);
                }
            }

            // Restore delivery slot availability
            if ($order->deliverySlot) {
                $order->deliverySlot->increment('available_slots', 1);
            }

            return back()->with('success', 'Je bestelling is succesvol geannuleerd.');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Er is een fout opgetreden bij het annuleren van je bestelling.');
        }
    }

    /**
     * Track order status
     */
    public function track(Order $order)
    {
        // Ensure user can only track their own orders
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Je hebt geen toegang tot deze bestelling.');
        }

        $order->load(['deliverySlot']);

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
     * Send order confirmation email
     */
    public function sendConfirmation(Order $order)
    {
        // Ensure user can only request confirmation for their own orders
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Je hebt geen toegang tot deze bestelling.');
        }

        try {
            // Here you would implement email sending
            // For now, we'll just return success
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
     * Get status display text
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
     * Get available status options for filtering
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
     * Check if order can be cancelled
     */
    private function canCancelOrder(Order $order): bool
    {
        return in_array($order->status, ['confirmed', 'pending']) && 
               $order->deliverySlot && 
               $order->deliverySlot->date > now()->addDay(); // Can cancel until 1 day before delivery
    }

    /**
     * Check if order can be tracked
     */
    private function canTrackOrder(Order $order): bool
    {
        return !in_array($order->status, ['cancelled']);
    }

    /**
     * Get estimated delivery time
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
     * Get tracking steps for order
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

        // If order is cancelled, mark all future steps as cancelled
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
     * Get current tracking step index
     */
    private function getCurrentTrackingStep(string $status): int
    {
        $stepMap = [
            'pending' => 0,
            'confirmed' => 1,
            'processing' => 2,
            'out_for_delivery' => 3,
            'delivered' => 4,
            'cancelled' => -1, // Special case
        ];

        return $stepMap[$status] ?? 0;
    }
}