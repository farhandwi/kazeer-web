<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\KitchenStation;
use App\Models\Staff;
use Illuminate\Support\Str;

class RestaurantSeeder extends Seeder
{
    public function run()
    {
        // Create sample restaurant
        $restaurant = Restaurant::create([
            'name' => 'Warung Makan Sederhana',
            'slug' => 'warung-makan-sederhana',
            'description' => 'Warung makan dengan cita rasa autentik Indonesia',
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 123, Jakarta',
            'open_time' => '08:00',
            'close_time' => '22:00',
            'is_active' => true,
        ]);

        // Create tables
        for ($i = 1; $i <= 20; $i++) {
            Table::create([
                'restaurant_id' => $restaurant->id,
                'table_number' => str_pad($i, 2, '0', STR_PAD_LEFT),
                'qr_code' => 'QR_' . $restaurant->id . '_' . str_pad($i, 2, '0', STR_PAD_LEFT) . '_' . time(),
                'capacity' => rand(2, 8),
                'status' => 'available',
            ]);
        }

        // Create kitchen stations
        $stations = [
            ['name' => 'Cold Station', 'description' => 'Salads, appetizers, desserts'],
            ['name' => 'Grill Station', 'description' => 'Grilled meats, seafood'],
            ['name' => 'Fry Station', 'description' => 'Fried foods, tempura'],
            ['name' => 'Wok Station', 'description' => 'Stir-fry, noodles'],
            ['name' => 'Soup Station', 'description' => 'Soups, stews, broths'],
        ];

        foreach ($stations as $stationData) {
            KitchenStation::create([
                'restaurant_id' => $restaurant->id,
                'name' => $stationData['name'],
                'description' => $stationData['description'],
            ]);
        }

        // Create categories
        $categories = [
            ['name' => 'Appetizers', 'slug' => 'appetizers'],
            ['name' => 'Main Course', 'slug' => 'main-course'],
            ['name' => 'Rice & Noodles', 'slug' => 'rice-noodles'],
            ['name' => 'Beverages', 'slug' => 'beverages'],
            ['name' => 'Desserts', 'slug' => 'desserts'],
        ];

        foreach ($categories as $index => $categoryData) {
            Category::create([
                'restaurant_id' => $restaurant->id,
                'name' => $categoryData['name'],
                'slug' => $categoryData['slug'],
                'sort_order' => $index + 1,
            ]);
        }

        // Create sample menu items
        $this->createSampleMenuItems($restaurant);

        // Create staff
        Staff::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Admin User',
            'email' => 'admin@warung.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'phone' => '081234567890',
        ]);

        Staff::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Kitchen Manager',
            'email' => 'kitchen@warung.com',
            'password' => bcrypt('password'),
            'role' => 'kitchen',
            'phone' => '081234567891',
        ]);

        // Create restaurant settings
        $settings = [
            'tax_rate' => '10',
            'service_charge_rate' => '5',
            'auto_confirm_orders' => 'false',
            'max_prep_time' => '60',
            'currency' => 'IDR',
        ];

        foreach ($settings as $key => $value) {
            $restaurant->settings()->create([
                'key' => $key,
                'value' => $value,
            ]);
        }
    }

    private function createSampleMenuItems($restaurant)
    {
        $categories = $restaurant->categories;
        $stations = $restaurant->kitchenStations;

        // Sample menu items dengan station assignments
        $menuItems = [
            // Appetizers
            [
                'category' => 'Appetizers',
                'name' => 'Gado-Gado',
                'price' => 25000,
                'preparation_time' => 10,
                'stations' => ['Cold Station'],
                'variants' => [
                    ['name' => 'Spice Level', 'value' => 'Mild', 'price_modifier' => 0],
                    ['name' => 'Spice Level', 'value' => 'Spicy', 'price_modifier' => 0],
                    ['name' => 'Extra Tofu', 'value' => 'Yes', 'price_modifier' => 3000],
                ]
            ],
            [
                'category' => 'Appetizers',
                'name' => 'Kerupuk Udang',
                'price' => 15000,
                'preparation_time' => 5,
                'stations' => ['Fry Station'],
            ],
            
            // Main Course
            [
                'category' => 'Main Course',
                'name' => 'Ayam Bakar',
                'price' => 45000,
                'preparation_time' => 25,
                'stations' => ['Grill Station'],
                'variants' => [
                    ['name' => 'Size', 'value' => 'Half', 'price_modifier' => -10000],
                    ['name' => 'Size', 'value' => 'Full', 'price_modifier' => 0],
                    ['name' => 'Spice Level', 'value' => 'Mild', 'price_modifier' => 0],
                    ['name' => 'Spice Level', 'value' => 'Hot', 'price_modifier' => 0],
                ]
            ],
            [
                'category' => 'Main Course',
                'name' => 'Ikan Gurame Goreng',
                'price' => 55000,
                'preparation_time' => 20,
                'stations' => ['Fry Station'],
            ],
            [
                'category' => 'Main Course',
                'name' => 'Rendang Daging',
                'price' => 50000,
                'preparation_time' => 15,
                'stations' => ['Soup Station'],
            ],
            
            // Rice & Noodles
            [
                'category' => 'Rice & Noodles',
                'name' => 'Nasi Gudeg',
                'price' => 35000,
                'preparation_time' => 12,
                'stations' => ['Cold Station'],
            ],
            [
                'category' => 'Rice & Noodles',
                'name' => 'Mie Ayam',
                'price' => 30000,
                'preparation_time' => 15,
                'stations' => ['Wok Station'],
                'variants' => [
                    ['name' => 'Noodle Type', 'value' => 'Regular', 'price_modifier' => 0],
                    ['name' => 'Noodle Type', 'value' => 'Pangsit', 'price_modifier' => 5000],
                    ['name' => 'Extra Meat', 'value' => 'Yes', 'price_modifier' => 8000],
                ]
            ],
            [
                'category' => 'Rice & Noodles',
                'name' => 'Nasi Goreng Kampung',
                'price' => 32000,
                'preparation_time' => 12,
                'stations' => ['Wok Station'],
            ],
            
            // Beverages
            [
                'category' => 'Beverages',
                'name' => 'Es Teh Manis',
                'price' => 8000,
                'preparation_time' => 3,
                'stations' => ['Cold Station'],
                'variants' => [
                    ['name' => 'Sugar Level', 'value' => 'Less Sweet', 'price_modifier' => 0],
                    ['name' => 'Sugar Level', 'value' => 'Normal', 'price_modifier' => 0],
                    ['name' => 'Sugar Level', 'value' => 'Extra Sweet', 'price_modifier' => 0],
                ]
            ],
            [
                'category' => 'Beverages',
                'name' => 'Jus Alpukat',
                'price' => 15000,
                'preparation_time' => 5,
                'stations' => ['Cold Station'],
            ],
            [
                'category' => 'Beverages',
                'name' => 'Kopi Tubruk',
                'price' => 12000,
                'preparation_time' => 5,
                'stations' => ['Cold Station'],
            ],
            
            // Desserts
            [
                'category' => 'Desserts',
                'name' => 'Es Campur',
                'price' => 18000,
                'preparation_time' => 8,
                'stations' => ['Cold Station'],
            ],
            [
                'category' => 'Desserts',
                'name' => 'Pisang Goreng',
                'price' => 12000,
                'preparation_time' => 10,
                'stations' => ['Fry Station'],
            ],
        ];

        foreach ($menuItems as $itemData) {
            $category = $categories->where('name', $itemData['category'])->first();
            
            $menuItem = MenuItem::create([
                'restaurant_id' => $restaurant->id,
                'category_id' => $category->id,
                'name' => $itemData['name'],
                'slug' => Str::slug($itemData['name']),
                'price' => $itemData['price'],
                'preparation_time' => $itemData['preparation_time'],
                'description' => 'Delicious ' . strtolower($itemData['name']) . ' prepared with authentic Indonesian spices',
                'is_available' => true,
            ]);

            // Assign stations
            if (isset($itemData['stations'])) {
                foreach ($itemData['stations'] as $index => $stationName) {
                    $station = $stations->where('name', $stationName)->first();
                    if ($station) {
                        $menuItem->kitchenStations()->attach($station->id, [
                            'preparation_order' => $index + 1
                        ]);
                    }
                }
            }

            // Create variants
            if (isset($itemData['variants'])) {
                foreach ($itemData['variants'] as $variant) {
                    $menuItem->variants()->create($variant);
                }
            }
        }
    }
}