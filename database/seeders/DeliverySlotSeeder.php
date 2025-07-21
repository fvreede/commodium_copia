<?php

namespace Database\Seeders;

use App\Models\DeliverySlot;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DeliverySlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * UITGEBREID: Maak bezorgslots aan voor de komende 2 maanden
     */
    public function run(): void
    {
        // Delete alleen toekomstige delivery slots om foreign key constraint problemen te voorkomen
        DeliverySlot::where('date', '>=', Carbon::today())->delete();

        $today = Carbon::today();
        
        // Genereer bezorgslots voor de komende 60 dagen (ongeveer 2 maanden)
        for ($i = 0; $i < 60; $i++) {
            $date = $today->copy()->addDays($i);

            // Sla zondagen over (geen bezorging beschikbaar)
            if ($date->isSunday()) {
                continue;
            }

            // Standaard bezorgslots voor alle dagen behalve zondag
            $this->createStandardSlots($date);

            // Extra slots voor zaterdag (premium ochtend slot)
            if ($date->isSaturday()) {
                $this->createSaturdayPremiumSlot($date);
            }

            // Extra slots voor vrijdag (weekend voorbereiding)
            if ($date->isFriday()) {
                $this->createFridayExtraSlot($date);
            }
        }

        $this->command->info('Created delivery slots for the next 60 days (2 months)');
        $this->command->info('Total slots created: ' . DeliverySlot::where('date', '>=', $today)->count());
    }

    /**
     * Maak standaard bezorgslots aan voor een gegeven dag
     * 
     * @param Carbon $date
     */
    private function createStandardSlots(Carbon $date): void
    {
        // Ochtend slot (09:00 - 12:00)
        DeliverySlot::create([
            'date' => $date,
            'start_time' => '09:00',
            'end_time' => '12:00',
            'price' => 4.95,
            'available_slots' => 10
        ]);

        // Middag slot (12:00 - 15:00)
        DeliverySlot::create([
            'date' => $date,
            'start_time' => '12:00',
            'end_time' => '15:00',
            'price' => 4.95,
            'available_slots' => 10
        ]);

        // Namiddag slot (15:00 - 18:00)
        DeliverySlot::create([
            'date' => $date,
            'start_time' => '15:00',
            'end_time' => '18:00',
            'price' => 4.95,
            'available_slots' => 10
        ]);

        // Avond slot (18:00 - 21:00) - premium pricing
        DeliverySlot::create([
            'date' => $date,
            'start_time' => '18:00',
            'end_time' => '21:00',
            'price' => 6.95,
            'available_slots' => 8
        ]);
    }

    /**
     * Maak premium zaterdag ochtend slot aan
     * 
     * @param Carbon $date
     */
    private function createSaturdayPremiumSlot(Carbon $date): void
    {
        // Vroege zaterdag ochtend slot (08:00 - 11:00) - premium
        DeliverySlot::create([
            'date' => $date,
            'start_time' => '08:00',
            'end_time' => '11:00',
            'price' => 7.95,
            'available_slots' => 5
        ]);
    }

    /**
     * Maak extra vrijdag slot aan voor weekend voorbereiding
     * 
     * @param Carbon $date
     */
    private function createFridayExtraSlot(Carbon $date): void
    {
        // Late vrijdag slot (21:00 - 23:00) - weekend prep
        DeliverySlot::create([
            'date' => $date,
            'start_time' => '21:00',
            'end_time' => '23:00',
            'price' => 8.95,
            'available_slots' => 6
        ]);
    }
}