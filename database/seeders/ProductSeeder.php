<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder {
    public function run(): void {
        $categories = Category::query()->orderBy('id')->get();
        $products = [
            ['name' => 'Basmati Rice 5 kg', 'price' => '780.00', 'unit' => 'bag', 'stock' => 24, 'threshold' => 6, 'category' => 'Pantry Staples'],
            ['name' => 'Red Lentils 1 kg', 'price' => '145.00', 'unit' => 'pack', 'stock' => 42, 'threshold' => 10, 'category' => 'Pantry Staples'],
            ['name' => 'Soybean Oil 2 litre', 'price' => '360.00', 'unit' => 'bottle', 'stock' => 18, 'threshold' => 5, 'category' => 'Pantry Staples'],
            ['name' => 'Fresh Bananas', 'price' => '120.00', 'unit' => 'dozen', 'stock' => 16, 'threshold' => 4, 'category' => 'Fresh Produce'],
            ['name' => 'Potatoes 2 kg', 'price' => '110.00', 'unit' => 'bag', 'stock' => 28, 'threshold' => 6, 'category' => 'Fresh Produce'],
            ['name' => 'Full Cream Milk 1 litre', 'price' => '95.00', 'unit' => 'carton', 'stock' => 30, 'threshold' => 8, 'category' => 'Dairy and Eggs'],
            ['name' => 'Farm Eggs 12 pieces', 'price' => '165.00', 'unit' => 'tray', 'stock' => 20, 'threshold' => 5, 'category' => 'Dairy and Eggs'],
            ['name' => 'Mineral Water 1.5 litre', 'price' => '35.00', 'unit' => 'bottle', 'stock' => 60, 'threshold' => 12, 'category' => 'Beverages'],
            ['name' => 'Instant Coffee 100 g', 'price' => '285.00', 'unit' => 'jar', 'stock' => 14, 'threshold' => 4, 'category' => 'Beverages'],
            ['name' => 'Dishwashing Liquid 500 ml', 'price' => '130.00', 'unit' => 'bottle', 'stock' => 22, 'threshold' => 5, 'category' => 'Household Cleaning'],
            ['name' => 'Laundry Detergent 2 kg', 'price' => '420.00', 'unit' => 'pack', 'stock' => 12, 'threshold' => 4, 'category' => 'Household Cleaning'],
            ['name' => 'Hand Wash 250 ml', 'price' => '155.00', 'unit' => 'bottle', 'stock' => 25, 'threshold' => 6, 'category' => 'Personal Care'],
            ['name' => 'Toothpaste 150 g', 'price' => '180.00', 'unit' => 'tube', 'stock' => 19, 'threshold' => 5, 'category' => 'Personal Care'],
            ['name' => 'Chocolate Biscuits snack pack', 'price' => '65.00', 'unit' => 'pack', 'stock' => 35, 'threshold' => 8, 'category' => 'Snacks'],
            ['name' => 'Roasted Peanuts 200 g', 'price' => '90.00', 'unit' => 'pack', 'stock' => 27, 'threshold' => 6, 'category' => 'Snacks'],
            ['name' => 'A4 Copy Paper 500 sheets', 'price' => '580.00', 'unit' => 'ream', 'stock' => 9, 'threshold' => 3, 'category' => 'Stationery'],
            ['name' => 'Parboiled Rice 5 kg', 'price' => '620.00', 'unit' => 'bag', 'stock' => 31, 'threshold' => 6, 'category' => 'Pantry Staples'],
            ['name' => 'Chickpeas 500 g', 'price' => '85.00', 'unit' => 'pack', 'stock' => 38, 'threshold' => 8, 'category' => 'Pantry Staples'],
            ['name' => 'Wheat Flour 2 kg', 'price' => '165.00', 'unit' => 'pack', 'stock' => 26, 'threshold' => 6, 'category' => 'Pantry Staples'],
            ['name' => 'White Sugar 1 kg', 'price' => '135.00', 'unit' => 'pack', 'stock' => 34, 'threshold' => 8, 'category' => 'Pantry Staples'],
            ['name' => 'Iodized Salt 1 kg', 'price' => '42.00', 'unit' => 'pack', 'stock' => 45, 'threshold' => 10, 'category' => 'Pantry Staples'],
            ['name' => 'Turmeric Powder 100 g', 'price' => '55.00', 'unit' => 'pack', 'stock' => 29, 'threshold' => 6, 'category' => 'Pantry Staples'],
            ['name' => 'Red Onions 1 kg', 'price' => '95.00', 'unit' => 'kg', 'stock' => 33, 'threshold' => 8, 'category' => 'Fresh Produce'],
            ['name' => 'Tomatoes 1 kg', 'price' => '90.00', 'unit' => 'kg', 'stock' => 25, 'threshold' => 6, 'category' => 'Fresh Produce'],
            ['name' => 'Carrots 1 kg', 'price' => '85.00', 'unit' => 'kg', 'stock' => 19, 'threshold' => 5, 'category' => 'Fresh Produce'],
            ['name' => 'Cucumbers 1 kg', 'price' => '75.00', 'unit' => 'kg', 'stock' => 22, 'threshold' => 5, 'category' => 'Fresh Produce'],
            ['name' => 'Green Chillies 250 g', 'price' => '35.00', 'unit' => 'pack', 'stock' => 18, 'threshold' => 5, 'category' => 'Fresh Produce'],
            ['name' => 'Low Fat Milk 1 litre', 'price' => '105.00', 'unit' => 'carton', 'stock' => 24, 'threshold' => 6, 'category' => 'Dairy and Eggs'],
            ['name' => 'Plain Yogurt 500 g', 'price' => '125.00', 'unit' => 'cup', 'stock' => 18, 'threshold' => 5, 'category' => 'Dairy and Eggs'],
            ['name' => 'Salted Butter 200 g', 'price' => '220.00', 'unit' => 'pack', 'stock' => 13, 'threshold' => 4, 'category' => 'Dairy and Eggs'],
            ['name' => 'Mozzarella Cheese 200 g', 'price' => '290.00', 'unit' => 'pack', 'stock' => 11, 'threshold' => 3, 'category' => 'Dairy and Eggs'],
            ['name' => 'Mango Juice 1 litre', 'price' => '190.00', 'unit' => 'carton', 'stock' => 21, 'threshold' => 5, 'category' => 'Beverages'],
            ['name' => 'Orange Juice 1 litre', 'price' => '210.00', 'unit' => 'carton', 'stock' => 18, 'threshold' => 5, 'category' => 'Beverages'],
            ['name' => 'Cola Soft Drink 2 litre', 'price' => '155.00', 'unit' => 'bottle', 'stock' => 26, 'threshold' => 6, 'category' => 'Beverages'],
            ['name' => 'Black Tea 200 g', 'price' => '175.00', 'unit' => 'box', 'stock' => 19, 'threshold' => 5, 'category' => 'Beverages'],
            ['name' => 'Floor Cleaner 1 litre', 'price' => '240.00', 'unit' => 'bottle', 'stock' => 15, 'threshold' => 4, 'category' => 'Household Cleaning'],
            ['name' => 'Glass Cleaner 500 ml', 'price' => '185.00', 'unit' => 'bottle', 'stock' => 17, 'threshold' => 4, 'category' => 'Household Cleaning'],
            ['name' => 'Toilet Cleaner 750 ml', 'price' => '210.00', 'unit' => 'bottle', 'stock' => 14, 'threshold' => 4, 'category' => 'Household Cleaning'],
            ['name' => 'Kitchen Sponge 3 pack', 'price' => '75.00', 'unit' => 'pack', 'stock' => 29, 'threshold' => 7, 'category' => 'Household Cleaning'],
            ['name' => 'Shampoo 400 ml', 'price' => '360.00', 'unit' => 'bottle', 'stock' => 18, 'threshold' => 5, 'category' => 'Personal Care'],
            ['name' => 'Hair Conditioner 200 ml', 'price' => '295.00', 'unit' => 'bottle', 'stock' => 14, 'threshold' => 4, 'category' => 'Personal Care'],
            ['name' => 'Bath Soap 100 g', 'price' => '55.00', 'unit' => 'bar', 'stock' => 48, 'threshold' => 12, 'category' => 'Personal Care'],
            ['name' => 'Face Wash 100 ml', 'price' => '260.00', 'unit' => 'tube', 'stock' => 12, 'threshold' => 3, 'category' => 'Personal Care'],
            ['name' => 'Potato Chips 100 g', 'price' => '75.00', 'unit' => 'pack', 'stock' => 36, 'threshold' => 8, 'category' => 'Snacks'],
            ['name' => 'Cheese Crackers 150 g', 'price' => '110.00', 'unit' => 'pack', 'stock' => 25, 'threshold' => 6, 'category' => 'Snacks'],
            ['name' => 'Salted Cashews 100 g', 'price' => '210.00', 'unit' => 'pack', 'stock' => 15, 'threshold' => 4, 'category' => 'Snacks'],
            ['name' => 'Ballpoint Pen Blue 10 pack', 'price' => '120.00', 'unit' => 'pack', 'stock' => 32, 'threshold' => 8, 'category' => 'Stationery'],
            ['name' => 'Spiral Notebook A5', 'price' => '135.00', 'unit' => 'piece', 'stock' => 21, 'threshold' => 5, 'category' => 'Stationery'],
            ['name' => 'HB Pencil 12 pack', 'price' => '95.00', 'unit' => 'box', 'stock' => 27, 'threshold' => 7, 'category' => 'Stationery'],
        ];

        foreach ($products as $product) {
            $category = $categories->firstWhere('name', $product['category']);
            Product::create([
                'user_id' => $category->user_id,
                'category_id' => $category->id,
                'name' => $product['name'],
                'price' => $product['price'],
                'unit' => $product['unit'],
                'stock' => $product['stock'],
                'low_stock_threshold' => $product['threshold'],
                'img_url' => 'images/default.jpg',
            ]);
        }
    }
}
