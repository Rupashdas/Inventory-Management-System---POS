<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder {
    public function run(): void {
        $users = User::query()->orderBy('id')->get();
        $customers = [
            ['name' => 'Green Leaf Cafe', 'email' => 'accounts@greenleaf.test', 'mobile' => '01811000001'],
            ['name' => 'Sunrise Office Supplies', 'email' => 'purchases@sunriseoffice.test', 'mobile' => '01811000002'],
            ['name' => 'Maya Residential Complex', 'email' => 'admin@mayaresidential.test', 'mobile' => '01811000003'],
            ['name' => 'Riverside Guest House', 'email' => 'frontdesk@riversideguest.test', 'mobile' => '01811000004'],
            ['name' => 'North Star Bakery', 'email' => 'orders@northstarbakery.test', 'mobile' => '01811000005'],
            ['name' => 'City Health Clinic', 'email' => 'billing@cityhealth.test', 'mobile' => '01811000006'],
        ];

        foreach ($customers as $index => $customer) {
            Customer::create(array_merge($customer, [
                'user_id' => $users[$index % $users->count()]->id,
            ]));
        }
    }
}
