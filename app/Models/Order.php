<?php

/**
 * Bestandsnaam: Order.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.4
 * Datum: 2025-07-01
 * Tijd: 01:25:04
 * Doel: Eloquent Model voor bestellingen in het e-commerce systeem. Beheert complete bestelling lifecycle van plaatsing tot bezorging, inclusief status management, betalingen en business logic.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /**
     * Attributen die mass assignable zijn
     * Deze velden kunnen veilig bulk toegewezen worden via create() of update()
     */
    protected $fillable = [
        'user_id',          // ID van de klant die de bestelling plaatste
        'delivery_slot_id', // ID van het gekozen bezorgslot
        'order_number',     // Uniek bestelnummer voor tracking
        'status',           // Huidige status van de bestelling
        'subtotal',         // Subtotaal van alle items excl. bezorgkosten
        'delivery_fee',     // Bezorgkosten voor dit bestelling
        'total',            // Totaalbedrag incl. bezorgkosten
        'payment_method',   // Gebruikte betaalmethode
        'payment_status',   // Status van de betaling
        'delivery_address', // Bezorgadres (JSON opslag)
        'order_notes',      // Optionele notities van de klant
        'order_date'        // Datum/tijd wanneer bestelling geplaatst werd
    ];

    /**
     * Attribute casting voor juiste data types
     * Zorgt voor automatische conversie tussen database en PHP types
     */
    protected $casts = [
        'subtotal' => 'decimal:2',       // Subtotaal met 2 decimalen
        'delivery_fee' => 'decimal:2',   // Bezorgkosten met 2 decimalen
        'total' => 'decimal:2',          // Totaal met 2 decimalen
        'delivery_address' => 'array',   // JSON adres als PHP array
        'order_date' => 'datetime'       // Besteldatum als Carbon instance
    ];

    /**
     * Bestelling status constanten
     * Definiëren alle mogelijke statussen voor een bestelling
     */
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PROCESSING = 'processing';
    const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Betaling status constanten
     * Definiëren alle mogelijke betalingsstatussen
     */
    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PROCESSING = 'processing';
    const PAYMENT_COMPLETED = 'completed';
    const PAYMENT_FAILED = 'failed';
    const PAYMENT_CANCELLED = 'cancelled';

    /**
     * Boot het model met automatische event handlers
     */
    protected static function boot()
    {
        parent::boot();

        // Automatisch order_date instellen bij aanmaak als niet al ingesteld
        static::creating(function ($order) {
            if (!$order->order_date) {
                $order->order_date = now();
            }
        });
    }

    /**
     * RELATIES
     */

    /**
     * Relatie naar de gebruiker/klant die deze bestelling plaatste
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relatie naar alle items in deze bestelling
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relatie naar het bezorgslot van deze bestelling
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function deliverySlot(): BelongsTo
    {
        return $this->belongsTo(DeliverySlot::class);
    }

    /**
     * ACCESSORS & MUTATORS
     */

    /**
     * Geformatteerd totaalbedrag in Nederlandse valuta format
     * 
     * @return string
     */
    public function getFormattedTotalAttribute(): string
    {
        return '€' . number_format((float) $this->total, 2, ',', '.');
    }

    /**
     * Geformatteerd subtotaal in Nederlandse valuta format
     * 
     * @return string
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return '€' . number_format((float) $this->subtotal, 2, ',', '.');
    }

    /**
     * Geformatteerde bezorgkosten in Nederlandse valuta format
     * 
     * @return string
     */
    public function getFormattedDeliveryFeeAttribute(): string
    {
        return '€' . number_format((float) $this->delivery_fee, 2, ',', '.');
    }

    /**
     * Nederlandse weergave van bestelling status
     * 
     * @return string
     */
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

    /**
     * Nederlandse weergave van betaling status
     * 
     * @return string
     */
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
     * BUSINESS LOGIC METHODS
     */

    /**
     * Bereken totaal van bestelling op basis van items en bezorgkosten
     * 
     * @return float
     */
    public function calculateTotal(): float
    {
        $itemsTotal = $this->items()->get()->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        return $itemsTotal + $this->delivery_fee;
    }

    /**
     * Controleer of bestelling geannuleerd kan worden
     * 
     * @return bool
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]) &&
            $this->deliverySlot &&
            $this->deliverySlot->date > now()->addDay();
    }

    /**
     * Controleer of bestelling gevolgd kan worden
     * 
     * @return bool
     */
    public function canBeTracked(): bool
    {
        return !in_array($this->status, [self::STATUS_CANCELLED]);
    }

    /**
     * Krijg geschatte bezorgtijd als leesbare tekst
     * 
     * @return string|null
     */
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

    /**
     * Krijg totaal aantal items in bestelling (inclusief hoeveelheden)
     * 
     * @return int
     */
    public function getTotalItemsCount(): int
    {
        return $this->items()->sum('quantity');
    }

    /**
     * Krijg aantal unieke items in bestelling
     * 
     * @return int
     */
    public function getUniqueItemsCount(): int
    {
        return $this->items()->count();
    }

    /**
     * QUERY SCOPES
     */

    /**
     * Scope voor actieve bestellingen (niet geannuleerd of bezorgd)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
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

    /**
     * Scope voor voltooide bestellingen
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    /**
     * Scope voor geannuleerde bestellingen
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope voor bestellingen van specifieke gebruiker
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope voor bestellingen met specifieke status
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope voor recente bestellingen (standaard 30 dagen)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope voor bestellingen met bezorging vandaag
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithDeliveryToday($query)
    {
        return $query->whereHas('deliverySlot', function ($q) {
            $q->where('date', today());
        });
    }

    /**
     * Scope voor zoeken op bestelnummer
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchByOrderNumber($query, $search)
    {
        return $query->where('order_number', 'like', "%{$search}%");
    }

    /**
     * STATUS MANAGEMENT METHODS
     */

    /**
     * Markeer bestelling als bevestigd
     * 
     * @return bool
     */
    public function markAsConfirmed(): bool
    {
        if (!in_array($this->status, [self::STATUS_PENDING])) {
            return false;
        }

        return $this->update(['status' => self::STATUS_CONFIRMED]);
    }

    /**
     * Markeer bestelling als wordt voorbereid
     * 
     * @return bool
     */
    public function markAsProcessing(): bool
    {
        if (!in_array($this->status, [self::STATUS_CONFIRMED])) {
            return false;
        }

        return $this->update(['status' => self::STATUS_PROCESSING]);
    }

    /**
     * Markeer bestelling als onderweg
     * 
     * @return bool
     */
    public function markAsOutForDelivery(): bool
    {
        if (!in_array($this->status, [self::STATUS_PROCESSING])) {
            return false;
        }

        return $this->update(['status' => self::STATUS_OUT_FOR_DELIVERY]);
    }

    /**
     * Markeer bestelling als bezorgd
     * 
     * @return bool
     */
    public function markAsDelivered(): bool
    {
        if (!in_array($this->status, [self::STATUS_OUT_FOR_DELIVERY])) {
            return false;
        }

        return $this->update(['status' => self::STATUS_DELIVERED]);
    }

    /**
     * Markeer bestelling als geannuleerd en herstel voorraad/slots
     * 
     * @return bool
     */
    public function markAsCancelled(): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        // Herstel product voorraad voor geannuleerde items
        foreach ($this->items as $item) {
            if ($item->product && $item->product->is_active) {
                $item->product->increment('stock_quantity', $item->quantity);
            }
        }

        // Herstel bezorgslot beschikbaarheid
        if ($this->deliverySlot) {
            $this->deliverySlot->increment('available_slots', 1);
        }

        return $this->update([
            'status' => self::STATUS_CANCELLED,
            'payment_status' => self::PAYMENT_CANCELLED
        ]);
    }

    /**
     * STATIC UTILITY METHODS
     */

    /**
     * Krijg alle geldige bestelling statussen
     * 
     * @return array
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
     * Krijg alle geldige betaling statussen
     * 
     * @return array
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