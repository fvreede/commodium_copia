<?php

/**
 * Bestandsnaam: Order.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.5
 * Datum: 2025-07-24
 * Tijd: 21:13:00
 * Doel: Eloquent Model voor bestellingen in het e-commerce systeem. Beheert complete bestelling lifecycle van plaatsing tot bezorging, inclusief status management, betalingen en business logic.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Order Model
 *
 * @property int $id De unieke identifier van de bestelling
 * @property int $user_id ID van de klant die de bestelling plaatste
 * @property int|null $delivery_slot_id ID van het gekozen bezorgslot
 * @property string $order_number Uniek bestelnummer voor tracking
 * @property string $status Huidige status van de bestelling
 * @property float $subtotal Subtotaal van alle items excl. bezorgkosten
 * @property float $delivery_fee Bezorgkosten voor dit bestelling
 * @property float $total Totaalbedrag incl. bezorgkosten
 * @property string|null $payment_method Gebruikte betaalmethode
 * @property string|null $payment_status Status van de betaling
 * @property array|null $delivery_address Bezorgadres (JSON opslag)
 * @property string|null $order_notes Optionele notities van de klant
 * @property \Illuminate\Support\Carbon|null $order_date Datum/tijd wanneer bestelling geplaatst werd
 * @property \Illuminate\Support\Carbon $created_at Aanmaakdatum
 * @property \Illuminate\Support\Carbon $updated_at Laatste wijzigingsdatum
 * @property-read \App\Models\User $user De klant die deze bestelling plaatste
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $items
 * @property-read \App\Models\DeliverySlot|null $deliverySlot Het bezorgslot van deze bestelling
 * @property-read string $formatted_total Geformatteerd totaalbedrag
 * @property-read string $formatted_subtotal Geformatteerd subtotaal
 * @property-read string $formatted_delivery_fee Geformatteerde bezorgkosten
 * @property-read string $status_display Nederlandse weergave van status
 * @property-read string $payment_status_display Nederlandse weergave van betaling status
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereDeliverySlotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereOrderNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereDeliveryFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereDeliveryAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereOrderNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereOrderDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUpdatedAt($value)
 * @method static Order|null find($id, $columns = ['*'])
 * @method static Order findOrFail($id, $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Collection|Order[] all($columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|Order active()
 * @method static \Illuminate\Database\Eloquent\Builder|Order completed()
 * @method static \Illuminate\Database\Eloquent\Builder|Order cancelled()
 * @method static \Illuminate\Database\Eloquent\Builder|Order forUser(int $userId)
 * @method static \Illuminate\Database\Eloquent\Builder|Order byStatus(string $status)
 * @method static \Illuminate\Database\Eloquent\Builder|Order recent(int $days = 30)
 * @method static \Illuminate\Database\Eloquent\Builder|Order withDeliveryToday()
 * @method static \Illuminate\Database\Eloquent\Builder|Order searchByOrderNumber(string $search)
 */
class Order extends Model
{
    /**
     * De database tabel geassocieerd met het model
     */
    protected $table = 'orders';

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
        'order_date' => 'datetime',      // Besteldatum als Carbon instance
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, \App\Models\Order>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relatie naar alle items in deze bestelling
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\OrderItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relatie naar het bezorgslot van deze bestelling
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\DeliverySlot, \App\Models\Order>
     */
    public function deliverySlot(): BelongsTo
    {
        return $this->belongsTo(DeliverySlot::class);
    }

    // Rest van je methoden blijven hetzelfde...
    // (ik toon alleen het begin vanwege lengte, voeg de rest van je bestaande methoden toe)

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

    // Voeg hier de rest van je bestaande methoden toe...
    // (calculateTotal, canBeCancelled, alle scopes, status management methods, etc.)
}