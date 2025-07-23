<?php

/**
 * Bestandsnaam: DeliverySlotSeeder.php (Database/Seeders)
 * Auteur: Fabio Vreede
 * Versie: v1.0.0
 * Datum: 2025-07-23
 * Tijd: 22:07
 * Doel: Database seeder voor het genereren van bezorgslots voor e-commerce platform. Creëert een
 *       uitgebreid schema van bezorgtijden voor de komende 2 maanden met verschillende pricing tiers,
 *       dag-specifieke slots, en capacity management. Bevat standaard dagslots, premium weekend opties,
 *       en speciale Friday night slots voor weekend voorbereiding. Ondersteunt business logica voor
 *       bezorgcapaciteit en dynamic pricing gebaseerd op dag/tijd combinaties.
 */

namespace Database\Seeders;

// Model imports voor delivery slot management
use App\Models\DeliverySlot;

// Carbon voor advanced datum/tijd manipulatie
use Carbon\Carbon;

// Laravel seeder base class
use Illuminate\Database\Seeder;

class DeliverySlotSeeder extends Seeder
{
    // ========== HOOFDSEEDER METHODE ==========

    /**
     * Voert database seeding uit voor delivery slots
     * 
     * Genereert een compleet schema van bezorgslots voor de komende 60 dagen met:
     * - Standaard dagslots (ma-za) met verschillende tijdsloten
     * - Premium pricing voor avond- en weekend slots  
     * - Zaterdag premium vroege slots
     * - Vrijdag late slots voor weekend voorbereiding
     * - Zondag exclusie (geen bezorging beschikbaar)
     * 
     * @return void
     */
    public function run(): void
    {
        // ========== CLEANUP BESTAANDE DATA ==========
        
        // Delete alleen toekomstige delivery slots om foreign key constraint problemen te voorkomen
        // Behoud historische data voor order referenties
        DeliverySlot::where('date', '>=', Carbon::today())->delete();
        
        // ========== DATUM RANGE SETUP ==========
        
        $today = Carbon::today();                    // Startdatum voor slot generatie
        
        // Genereer bezorgslots voor de komende 60 dagen (ongeveer 2 maanden)
        // Biedt voldoende vooruitplanning voor klanten en business operations
        for ($i = 0; $i < 60; $i++) {
            $date = $today->copy()->addDays($i);     // Nieuwe datum instantie voor elke iteratie
            
            // ========== DAG SPECIFIEKE LOGICA ==========
            
            // Sla zondagen over (geen bezorging beschikbaar op zondagen)
            if ($date->isSunday()) {
                continue;
            }
            
            // Creëer standaard bezorgslots voor alle beschikbare dagen (ma-za)
            $this->createStandardSlots($date);
            
            // ========== PREMIUM WEEKEND SLOTS ==========
            
            // Extra premium slots voor zaterdag (vroege ochtend voor weekend shoppers)
            if ($date->isSaturday()) {
                $this->createSaturdayPremiumSlot($date);
            }
            
            // ========== VRIJDAG WEEKEND PREP SLOTS ==========
            
            // Extra late slots voor vrijdag (weekend voorbereiding bezorgingen)
            if ($date->isFriday()) {
                $this->createFridayExtraSlot($date);
            }
        }
        
        // ========== SEEDING FEEDBACK ==========
        
        // Console output voor seeding bevestiging
        $this->command->info('Created delivery slots for the next 60 days (2 months)');
        
        // Totaal aantal aangemaakte slots voor verificatie
        $totalSlots = DeliverySlot::where('date', '>=', $today)->count();
        $this->command->info('Total slots created: ' . $totalSlots);
    }

    // ========== STANDAARD SLOT CREATIE ==========

    /**
     * Creëert standaard bezorgslots voor een gegeven dag
     * 
     * Genereert 4 standaard tijdslots per dag met verschillende pricing:
     * - 3 reguliere slots (ochtend, middag, namiddag) à €4.95
     * - 1 premium avondslot à €6.95 (hogere prijs, minder capaciteit)
     * 
     * Elke slot heeft verschillende capaciteit gebaseerd op verwachte vraag
     * 
     * @param Carbon $date - De datum waarvoor slots aangemaakt worden
     * @return void
     */
    private function createStandardSlots(Carbon $date): void
    {
        // ========== OCHTEND SLOT ==========
        
        // Ochtend bezorging (09:00 - 12:00) - populair voor werkende mensen
        DeliverySlot::create([
            'date' => $date,                         // Bezorgdatum
            'start_time' => '09:00',                 // Start tijd van bezorgvenster
            'end_time' => '12:00',                   // Eind tijd van bezorgvenster  
            'price' => 4.95,                        // Standaard bezorgprijs in euros
            'available_slots' => 10                  // Maximaal aantal beschikbare bezorgingen
        ]);

        // ========== MIDDAG SLOT ==========
        
        // Middag bezorging (12:00 - 15:00) - lunch periode en thuiswerkers
        DeliverySlot::create([
            'date' => $date,
            'start_time' => '12:00',
            'end_time' => '15:00', 
            'price' => 4.95,                        // Zelfde prijs als ochtend
            'available_slots' => 10                  // Zelfde capaciteit als ochtend
        ]);

        // ========== NAMIDDAG SLOT ==========
        
        // Namiddag bezorging (15:00 - 18:00) - na-school periode
        DeliverySlot::create([
            'date' => $date,
            'start_time' => '15:00',
            'end_time' => '18:00',
            'price' => 4.95,                        // Standaard pricing tier
            'available_slots' => 10                  // Standaard capaciteit
        ]);

        // ========== PREMIUM AVOND SLOT ==========
        
        // Avond bezorging (18:00 - 21:00) - premium pricing voor convenience
        DeliverySlot::create([
            'date' => $date,
            'start_time' => '18:00',
            'end_time' => '21:00',
            'price' => 6.95,                        // Premium prijs (+€2.00) voor avond convenience
            'available_slots' => 8                   // Beperkte capaciteit (minder bezorgers beschikbaar)
        ]);
    }

    // ========== ZATERDAG PREMIUM SLOTS ==========

    /**
     * Creëert premium vroege zaterdag ochtend slot
     * 
     * Speciaal slot voor weekend shoppers die vroeg hun boodschappen willen ontvangen.
     * Hogere prijs vanwege vroege bezorgtijd en weekend premium.
     * Beperkte capaciteit vanwege specifieke doelgroep en operationele kosten.
     * 
     * @param Carbon $date - Zaterdag datum voor premium slot
     * @return void
     */
    private function createSaturdayPremiumSlot(Carbon $date): void
    {
        // ========== VROEGE WEEKEND SLOT ==========
        
        // Vroege zaterdag ochtend (08:00 - 11:00) - premium weekend service
        DeliverySlot::create([
            'date' => $date,
            'start_time' => '08:00',                 // Extra vroeg voor weekend convenience
            'end_time' => '11:00',                   // 3 uur window voor flexibiliteit
            'price' => 7.95,                        // Premium pricing (+€3.00) voor vroege weekend bezorging
            'available_slots' => 5                   // Beperkte capaciteit (exclusieve service)
        ]);
    }

    // ========== VRIJDAG WEEKEND PREP SLOTS ==========

    /**
     * Creëert extra laat vrijdag slot voor weekend voorbereiding
     * 
     * Speciaal slot voor klanten die hun weekend boodschappen vroeg willen ontvangen.
     * Late avond bezorging op vrijdag voor weekend party/event voorbereiding.
     * Hoogste pricing tier vanwege late tijdstip en weekend prep premium.
     * 
     * @param Carbon $date - Vrijdag datum voor weekend prep slot
     * @return void
     */
    private function createFridayExtraSlot(Carbon $date): void
    {
        // ========== LATE WEEKEND PREP SLOT ==========
        
        // Late vrijdag bezorging (21:00 - 23:00) - weekend voorbereiding service
        DeliverySlot::create([
            'date' => $date,
            'start_time' => '21:00',                 // Late avond voor weekend prep
            'end_time' => '23:00',                   // 2 uur window (korter vanwege late tijd)
            'price' => 8.95,                        // Hoogste pricing tier (+€4.00) voor late/premium service
            'available_slots' => 6                   // Matige capaciteit (gespecialiseerde service)
        ]);
    }
}