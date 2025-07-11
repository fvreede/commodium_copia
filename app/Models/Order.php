<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model

{
    protected $fillable = [
        'user_id', 
        'delivery_slot_id', 
        'order_number',
        'status', 
        'subtotal',
        'delivery_fee',
        'total',
        'payment_method',
        'payment_status',
        'delivery_address',
        'order_notes',
        'order_date'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total' => 'decimal:2',
        'delivery_address' => 'array',
        'order_date' => 'datetime'
    ];

    // Order statuses
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PROCESSING = 'processing';
    const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    // Payment statuses
    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PROCESSING = 'processing';
    const PAYMENT_COMPLETED = 'completed';
    const PAYMENT_FAILED = 'failed';
    const PAYMENT_CANCELLED = 'cancelled';

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            // Set order_date if not already set
            if (!$order->order_date) {
                $order->order_date = now();
            }
        });
    }

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function deliverySlot(): BelongsTo
    {
        return $this->belongsTo(DeliverySlot::class);
    }

    /**
     * Accessors & Mutators
     */
    public function getFormattedTotalAttribute(): string
    {
        return '€' . number_format((float) $this->total, 2, ',', '.');
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return '€' . number_format((float) $this->subtotal, 2, ',', '.');
    }

    public function getFormattedDeliveryFeeAttribute(): string
    {
        return '€' . number_format((float) $this->delivery_fee, 2, ',', '.');
    }

    public function getStatusDisplayAttribute(): string
    {
        $statuses = [
            self::STATUS_PENDING => 'In behandeling',
            self::STATUS_CONFIRMED => 'Bevestigd',
            self::STATUS_PROCESSING => 'Wordt voorbereid',
            self::STATUS_OUT_FOR_DELIVERY => 'Onderweg',
            self::STATUS_DELIVERED => 'Bezorgd',
            self::STATUS_CANCELLED => 'Geannuleerd',
        ];

        return $statuses[$this->status] ?? 'Onbekend';
    }

    public function getPaymentStatusDisplayAttribute(): string
    {
        $statuses = [
            self::PAYMENT_PENDING => 'In behandeling',
            self::PAYMENT_PROCESSING => 'Wordt verwerkt',
            self::PAYMENT_COMPLETED => 'Betaald',
            self::PAYMENT_FAILED => 'Mislukt',
            self::PAYMENT_CANCELLED => 'Geannuleerd',
        ];

        return $statuses[$this->payment_status] ?? 'Onbekend';
    }

    /**
     * Business Logic Methods
     */
    public function calculateTotal(): float
    {
        $itemsTotal = $this->items()->get()->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        return $itemsTotal + $this->delivery_fee;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]) && 
               $this->deliverySlot && 
               $this->deliverySlot->date > now()->addDay();
    }

    public function canBeTracked(): bool
    {
        return !in_array($this->status, [self::STATUS_CANCELLED]);
    }

    public function getEstimatedDelivery(): ?string
    {
        if (!$this->deliverySlot) {
            return null;
        }

        return $this->deliverySlot->date->format('l, j F Y') . 
               ' tussen ' . 
               $this->deliverySlot->start_time . 
               ' en ' . 
               $this->deliverySlot->end_time;
    }

    public function getTotalItemsCount(): int
    {
        return $this->items()->sum('quantity');
    }

    public function getUniqueItemsCount(): int
    {
        return $this->items()->count();
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_PROCESSING,
            self::STATUS_OUT_FOR_DELIVERY
        ]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeWithDeliveryToday($query)
    {
        return $query->whereHas('deliverySlot', function ($q) {
            $q->where('date', today());
        });
    }

    public function scopeSearchByOrderNumber($query, $search)
    {
        return $query->where('order_number', 'like', "%{$search}%");
    }

    /**
     * Status Management Methods
     */
    public function markAsConfirmed(): bool
    {
        if (!in_array($this->status, [self::STATUS_PENDING])) {
            return false;
        }

        return $this->update(['status' => self::STATUS_CONFIRMED]);
    }

    public function markAsProcessing(): bool
    {
        if (!in_array($this->status, [self::STATUS_CONFIRMED])) {
            return false;
        }

        return $this->update(['status' => self::STATUS_PROCESSING]);
    }

    public function markAsOutForDelivery(): bool
    {
        if (!in_array($this->status, [self::STATUS_PROCESSING])) {
            return false;
        }

        return $this->update(['status' => self::STATUS_OUT_FOR_DELIVERY]);
    }

    public function markAsDelivered(): bool
    {
        if (!in_array($this->status, [self::STATUS_OUT_FOR_DELIVERY])) {
            return false;
        }

        return $this->update(['status' => self::STATUS_DELIVERED]);
    }

    public function markAsCancelled(): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        // Restore product stock
        foreach ($this->items as $item) {
            if ($item->product && $item->product->is_active) {
                $item->product->increment('stock_quantity', $item->quantity);
            }
        }

        // Restore delivery slot availability
        if ($this->deliverySlot) {
            $this->deliverySlot->increment('available_slots', 1);
        }

        return $this->update([
            'status' => self::STATUS_CANCELLED,
            'payment_status' => self::PAYMENT_CANCELLED
        ]);
    }

    /**
     * Get all valid statuses
     */
    public static function getValidStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_PROCESSING,
            self::STATUS_OUT_FOR_DELIVERY,
            self::STATUS_DELIVERED,
            self::STATUS_CANCELLED,
        ];
    }

    /**
     * Get all valid payment statuses
     */
    public static function getValidPaymentStatuses(): array
    {
        return [
            self::PAYMENT_PENDING,
            self::PAYMENT_PROCESSING,
            self::PAYMENT_COMPLETED,
            self::PAYMENT_FAILED,
            self::PAYMENT_CANCELLED,
        ];
    }
}