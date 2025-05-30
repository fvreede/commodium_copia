<?php

namespace Database\Seeders;

use App\Models\Category;
// use App\Models\Subcategory;
// use App\Models\Product;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        $mockDataPath = resource_path('js/Data/mockData.json');
        $mockData = json_decode(file_get_contents($mockDataPath), true);

        foreach ($mockData as $categoryData) {
            $category = Category::create([
                'name' => $categoryData['name'],
                //'banner_path' => str_replace('assets/', '', $categoryData['bannerSrc']),
                'banner_path' => $categoryData['bannerSrc'],
            ]);

            foreach ($categoryData['subcategories'] as $subcategoryData) {
                $subcategory = Subcategory::create([
                    'category_id' => $category->id,
                    'name' => $subcategoryData['name'],
                ]);

                foreach ($subcategoryData['products'] as $productData) {
                    Product::create([
                        'subcategory_id' => $subcategory->id,
                        'name' => $productData['name'],
                        'description' => $productData['description'],
                        'fullDescription' => $productData['fullDescription'],
                        'price' => $productData['price'],
                        //'image_path' => str_replace('assets/', '',$productData['imageSrc']),
                        'image_path' => $productData['imageSrc'],
                    ]);
                }
            }
        }
        */

        $categories = [
            [
                'name' => 'Groenten en Fruit',
                'banner_path' => 'images/subcategories/banners/groentenFruitBanner.jpg',
                'description' => 'Ontdek de lekkerste groenten en fruit van het seizoen.',
                'image_path' => 'images/categories/groenten-fruit.jpg'
            ],
            [
                'name' => 'Bakkerij en Brood',
                'banner_path' => 'images/subcategories/banners/bakkerijBroodBanner.jpg',
                'description' => 'Vers brood en gebak, elke dag vers gebakken.',
                'image_path' => 'images/categories/bakkerij-brood.jpg'
            ],
            [
                'name' => 'Zuivel en Eieren',
                'banner_path' => 'images/subcategories/banners/zuivelEierenBanner.jpg',
                'description' => 'Verse zuivel en eieren, rechtstreeks van de boerderij.',
                'image_path' => 'images/categories/zuivel-eieren.jpg'
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
