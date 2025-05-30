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
        DeliverySlot::truncate();

        // Time slots configuration
        $timeSlots = [
            ['start' => '08:00', 'end' => '22:00', 'price' => 4.95],
            ['start' => '16:00', 'end' => '22:00', 'price' => 6.95],
            ['start' => '19:00', 'end' => '21:00', 'price' => 7.50],
            ['start' => '20:00', 'end' => '22:00', 'price' => 7.50],
        ];

        // Create slots for the next 7 days
        for ($i = 0; $i < 7; $i++) {
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
                    'price' => $slot['price']
                ]);
            }
        }
    }
}