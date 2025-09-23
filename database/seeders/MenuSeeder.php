<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('menu_option_categories')->insert([
            [
                'name' => 'Tingkat Pedas',
                'slug' => 'spice-level',
                'type' => 'single',
                'is_required' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'Toping Extra',
                'slug' => 'extra-toppings',
                'type' => 'multiple',
                'is_required' => false,
                'sort_order' => 2
            ],
            [
                'name' => 'Ukuran Porsi',
                'slug' => 'portion-size',
                'type' => 'single',
                'is_required' => true,
                'sort_order' => 3
            ]
        ]);

    // Seed untuk menu options
    DB::table('menu_options')->insert([
        // Tingkat Pedas
        ['option_category_id' => 1, 'name' => 'Tidak Pedas', 'slug' => 'no-spice', 'additional_price' => 0, 'sort_order' => 1],
        ['option_category_id' => 1, 'name' => 'Pedas Level 1', 'slug' => 'mild', 'additional_price' => 0, 'sort_order' => 2],
        ['option_category_id' => 1, 'name' => 'Pedas Level 2', 'slug' => 'medium', 'additional_price' => 0, 'sort_order' => 3],
        ['option_category_id' => 1, 'name' => 'Pedas Level 3', 'slug' => 'hot', 'additional_price' => 0, 'sort_order' => 4],
        ['option_category_id' => 1, 'name' => 'Extra Pedas', 'slug' => 'very-hot', 'additional_price' => 0, 'sort_order' => 5],
        
        // Toping Extra
        ['option_category_id' => 2, 'name' => 'Extra Keju', 'slug' => 'extra-cheese', 'additional_price' => 5000, 'sort_order' => 1],
        ['option_category_id' => 2, 'name' => 'Extra Daging', 'slug' => 'extra-meat', 'additional_price' => 10000, 'sort_order' => 2],
        ['option_category_id' => 2, 'name' => 'Extra Sayuran', 'slug' => 'extra-vegetables', 'additional_price' => 3000, 'sort_order' => 3],
        ['option_category_id' => 2, 'name' => 'Extra Sambal', 'slug' => 'extra-chili', 'additional_price' => 2000, 'sort_order' => 4],
        
        // Ukuran Porsi
        ['option_category_id' => 3, 'name' => 'Regular', 'slug' => 'regular', 'additional_price' => 0, 'sort_order' => 1],
        ['option_category_id' => 3, 'name' => 'Large', 'slug' => 'large', 'additional_price' => 8000, 'sort_order' => 2],
        ['option_category_id' => 3, 'name' => 'Extra Large', 'slug' => 'extra-large', 'additional_price' => 15000, 'sort_order' => 3],
    ]);

    // Seed untuk discount (contoh)
    DB::table('discounts')->insert([
        [
            'name' => 'Flash Sale September',
            'code' => 'FLASH50',
            'description' => 'Diskon 50% untuk semua makanan',
            'type' => 'percentage',
            'value' => 50.00,
            'minimum_order' => 25000.00,
            'maximum_discount' => 50000.00,
            'starts_at' => '2025-09-01 05:00:00',
            'expires_at' => '2025-09-02 15:00:00',
            'usage_limit' => 100,
            'usage_limit_per_customer' => 1,
            'is_active' => true,
            'customer_eligibility' => 'all'
        ],
        [
            'name' => 'Welcome Discount',
            'code' => 'WELCOME20',
            'description' => 'Diskon Rp 20,000 untuk pelanggan baru',
            'type' => 'fixed_amount',
            'value' => 20000.00,
            'minimum_order' => 50000.00,
            'maximum_discount' => null, // tambahkan supaya konsisten
            'starts_at' => '2025-09-01 00:00:00',
            'expires_at' => '2025-12-31 23:59:59',
            'usage_limit' => null, // tambahkan supaya konsisten
            'usage_limit_per_customer' => 1,
            'is_active' => true,
            'customer_eligibility' => 'new_customers'
        ]
    ]);
    
    }
}
