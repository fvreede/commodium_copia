<?php

/**
 * Bestandsnaam: DeliverySlot.php
 * Auteur: Fabio Vreede
 * Versie: v1.0.5
 * Datum: 2025-06-20
 * Tijd: 23:57:21
 * Doel: Eloquent Model voor bezorgslots in het bestelsysteem. Beheert beschikbare bezorgtijden, capaciteit en real-time beschikbaarheid controle voor optimale bezorgplanning.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliverySlot extends Model
{
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
        'available_slots' => 'integer'  // Aantal slots als geheel getal
    ];

    /**
     * Relatie naar alle bestellingen die dit bezorgslot gebruiken
     * Een bezorgslot kan meerdere bestellingen hebben (One-to-Many)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
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
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable($query)
    {
        return $query->where('date', '>=', now()->startOfDay())  // Vanaf vandaag
            ->where('available_slots', '>', 0)                   // Met beschikbare slots
            ->orderBy('date')                                    // Sorteer op datum
            ->orderBy('start_time');                             // Dan op starttijd
    }
}