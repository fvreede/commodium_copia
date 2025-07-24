<?php

/**
 * Bestandsnaam: DeliverySlot.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.8
 * Datum: 2025-07-24
 * Tijd: 21:15:00
 * Doel: Eloquent Model voor bezorgslots in het bestelsysteem. Beheert beschikbare bezorgtijden, capaciteit en real-time beschikbaarheid controle voor optimale bezorgplanning.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * DeliverySlot Model
 *
 * @property int $id De unieke identifier van het bezorgslot
 * @property \Illuminate\Support\Carbon $date Bezorgdatum voor dit tijdslot
 * @property string $start_time Starttijd van het bezorgvenster
 * @property string $end_time Eindtijd van het bezorgvenster
 * @property float $price Bezorgkosten voor dit tijdslot
 * @property int $available_slots Maximaal aantal bestellingen dat dit slot kan verwerken
 * @property \Illuminate\Support\Carbon $created_at Aanmaakdatum
 * @property \Illuminate\Support\Carbon $updated_at Laatste wijzigingsdatum
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 *
 * @method static \Illuminate\Database\Eloquent\Builder|DeliverySlot newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DeliverySlot newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DeliverySlot query()
 * @method static \Illuminate\Database\Eloquent\Builder|DeliverySlot whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeliverySlot whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeliverySlot whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeliverySlot whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeliverySlot wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeliverySlot whereAvailableSlots($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeliverySlot whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeliverySlot whereUpdatedAt($value)
 * @method static DeliverySlot|null find($id, $columns = ['*'])
 * @method static DeliverySlot findOrFail($id, $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Collection|DeliverySlot[] all($columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|DeliverySlot available()
 */
class DeliverySlot extends Model
{
    /**
     * De database tabel geassocieerd met het model
     */
    protected $table = 'delivery_slots';

    /**
     * Attributen die mass assignable zijn
     * Deze velden kunnen veilig bulk toegewezen worden via create() of update()
     */
    protected $fillable = [
        'date',             // Bezorgdatum voor dit tijdslot
        'start_time',       // Starttijd van het bezorgvenster
        'end_time',         // Eindtijd van het bezorgvenster
        'price',            // Bezorgkosten voor dit tijdslot
        'available_slots'   // Maximaal aantal bestellingen dat dit slot kan verwerken
    ];

    /**
     * Attribute casting voor juiste data types
     * Zorgt voor automatische conversie tussen database en PHP types
     */
    protected $casts = [
        'date' => 'date',           // Datum als Carbon instance voor makkelijke manipulatie
        'price' => 'decimal:2',     // Prijs met 2 decimalen voor nauwkeurigheid
        'available_slots' => 'integer',  // Aantal slots als geheel getal
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relatie naar alle bestellingen die dit bezorgslot gebruiken
     * Een bezorgslot kan meerdere bestellingen hebben (One-to-Many)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Order>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Controleer of dit bezorgslot beschikbaar is voor nieuwe bestellingen
     * Controleert zowel datum als beschikbare capaciteit
     * 
     * @return bool
     */
    public function isAvailable(): bool
    {
        // Slot is beschikbaar als het vandaag of in de toekomst is EN er nog slots beschikbaar zijn
        return $this->date >= today() && $this->available_slots > 0;
    }

    /**
     * Bereken real-time beschikbare slots (rekening houdend met bestaande bestellingen)
     * Trekt geboekte bestellingen af van totale capaciteit
     * 
     * @return int
     */
    public function getCurrentAvailableSlots(): int
    {
        // Tel alle bestellingen die niet geannuleerd zijn voor dit slot
        $bookedSlots = $this->orders()->where('status', '!=', 'cancelled')->count();
        
        // Return beschikbare slots (minimaal 0, kan niet negatief zijn)
        return max(0, $this->available_slots - $bookedSlots);
    }

    /**
     * Query scope om alleen beschikbare bezorgslots op te halen
     * Filtert op datum (vandaag of later) en beschikbaarheid, gesorteerd op datum/tijd
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeAvailable($query): void
    {
        $query->where('date', '>=', now()->startOfDay())  // Vanaf vandaag
            ->where('available_slots', '>', 0)                   // Met beschikbare slots
            ->orderBy('date')                                    // Sorteer op datum
            ->orderBy('start_time');                             // Dan op starttijd
    }
}