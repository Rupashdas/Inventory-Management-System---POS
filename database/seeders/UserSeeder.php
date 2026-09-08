<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder {
    public function run(): void {
        $users = [
            ['firstName' => 'Aisha', 'lastName' => 'Rahman', 'email' => 'aisha.rahman@inventory.test', 'mobile' => '01711000001'],
            ['firstName' => 'Tanvir', 'lastName' => 'Hossain', 'email' => 'tanvir.hossain@inventory.test', 'mobile' => '01711000002'],
            ['firstName' => 'Nusrat', 'lastName' => 'Jahan', 'email' => 'nusrat.jahan@inventory.test', 'mobile' => '01711000003'],
        ];

        foreach ($users as $user) {
            User::create(array_merge($user, [
                'otp' => 0,
                'password' => Hash::make('pass123'),
            ]));
        }
    }
}
