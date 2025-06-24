<?php

namespace Database\Seeders;

use App\Models\DeliverySlot;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DeliverySlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete only future delivery slots to avoid foreign key constraint issues with existing orders
        DeliverySlot::where('date', '>=', Carbon::today())->delete();

        $today = Carbon::today();

        // Generate delivery slots for the next 14 days
        for ($i = 0; $i < 14; $i++) {
            $date = $today->copy()->addDays($i);

            // Skip Sundays (no delivery available)
            if ($date->isSunday()) {
                continue;
            }

            // Standard delivery slots
            DeliverySlot::create([
                'date' => $date,
                'start_time' => '09:00',
                'end_time' => '12:00',
                'price' => 4.95,
                'available_slots' => 10
            ]);

            DeliverySlot::create([
                'date' => $date,
                'start_time' => '12:00',
                'end_time' => '15:00',
                'price' => 4.95,
                'available_slots' => 10
            ]);

            DeliverySlot::create([
                'date' => $date,
                'start_time' => '15:00',
                'end_time' => '18:00',
                'price' => 4.95,
                'available_slots' => 10
            ]);

            // Evening delivery slot (premium pricing)
            DeliverySlot::create([
                'date' => $date,
                'start_time' => '18:00',
                'end_time' => '21:00',
                'price' => 6.95,
                'available_slots' => 8
            ]);

            // Add Saturday morning premium slot
            if ($date->isSaturday()) {
                DeliverySlot::create([
                    'date' => $date,
                    'start_time' => '08:00',
                    'end_time' => '11:00',
                    'price' => 7.95,
                    'available_slots' => 5
                ]);
            }
        }

        $this->command->info('Created delivery slots for the next 14 days');
    }
}