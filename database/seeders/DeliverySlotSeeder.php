<?php

namespace Database\Seeders;

use App\Models\DeliverySlot;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DeliverySlotSeeder extends Seeder
{
    public function run()
    {
        // Clear existing slots
        DeliverySlot::query()->delete();

        // Time slots configuration
        $timeSlots = [
            ['start' => '08:00', 'end' => '12:00', 'price' => 4.95, 'capacity' => 10],
            ['start' => '12:00', 'end' => '16:00', 'price' => 4.95, 'capacity' => 10],
            ['start' => '16:00', 'end' => '20:00', 'price' => 6.95, 'capacity' => 8],
            ['start' => '19:00', 'end' => '21:00', 'price' => 7.50, 'capacity' => 5],
            ['start' => '20:00', 'end' => '22:00', 'price' => 7.50, 'capacity' => 5],
        ];

        // Create slots for the next 14 days
        for ($i = 0; $i < 14; $i++) {
            $date = Carbon::today()->addDays($i);
            
            // Skip Sundays (no delivery)
            if ($date->isSunday()) {
                continue;
            }

            foreach ($timeSlots as $slot) {
                DeliverySlot::create([
                    'date' => $date,
                    'start_time' => $slot['start'],
                    'end_time' => $slot['end'],
                    'price' => $slot['price'],
                    'available_slots' => $slot['capacity']
                ]);
            }
        }
    }
}