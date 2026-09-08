<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder {
    public function run(): void {
        $userIds = User::query()->pluck('id')->values();
        $categories = [
            'Pantry Staples',
            'Fresh Produce',
            'Dairy and Eggs',
            'Beverages',
            'Household Cleaning',
            'Personal Care',
            'Snacks',
            'Stationery',
        ];

        foreach ($categories as $index => $name) {
            Category::create([
                'name' => $name,
                'user_id' => $userIds[$index % $userIds->count()],
            ]);
        }
    }
}
