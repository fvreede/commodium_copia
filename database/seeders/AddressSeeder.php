<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserAddress;

class AddressSeeder extends Seeder
{
    public function run()
    {
        // Get the specific user by email
        $user = User::where('email', 'fvreede@gmail.com')->first();
        
        if ($user) {
            // Create a test address
            UserAddress::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'street' => 'Grote Markt',
                    'house_number' => '42',
                    'postal_code' => '2011 RJ',
                    'city' => 'Haarlem',
                    'country' => 'Nederland'
                ]
            );
            
            echo "Test address created for user: {$user->email}\n";
        } else {
            echo "User with email fvreede@gmail.com not found!\n";
            echo "Available users:\n";
            foreach(User::all() as $availableUser) {
                echo "- {$availableUser->email}\n";
            }
        }
    }
}