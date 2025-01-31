<?php

namespace Database\Seeders;

use App\Models\Subcategory;
use Illuminate\Database\Seeder;

class SubcategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subcategories = [
            // Groenten en Fruit
            [
                'category_id' => 1,
                'name' => 'Groenten'
            ],
            [
                'category_id' => 1,
                'name' => 'Fruit'
            ],
            // Bakkerij en Brood
            [
                'category_id' => 2,
                'name' => 'Brood'
            ],
            [
                'category_id' => 2,
                'name' => 'Gebak'
            ],
            // Zuivel en Eieren
            [
                'category_id' => 3,
                'name' => 'Zuivel'
            ],
            [
                'category_id' => 3,
                'name' => 'Kaas'
            ],
            [
                'category_id' => 3,
                'name' => 'Eieren'
            ],
        ];

        foreach ($subcategories as $subcategory) {
            Subcategory::create($subcategory);
        }
    }
}
